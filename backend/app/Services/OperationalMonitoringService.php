<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OperationalMonitoringService
{
    public function __construct(
        private readonly LogManager $logs,
    ) {}

    public function recordResponse(Request $request, Response $response, float $durationMs): void
    {
        if (! $this->enabled()) {
            return;
        }

        $status = $response->getStatusCode();
        if ($status >= 500) {
            $this->warning('api.5xx', $this->requestContext($request, $durationMs, $status));

            return;
        }

        if ($durationMs >= $this->slowRequestThreshold()) {
            $this->warning('api.slow', $this->requestContext($request, $durationMs, $status));
        }
    }

    public function recordException(Request $request, Throwable $exception, float $durationMs): void
    {
        if (! $this->enabled()) {
            return;
        }

        $this->warning('api.exception', [
            ...$this->requestContext($request, $durationMs, 500),
            'exception_class' => $exception::class,
        ]);
    }

    public function recordFailedJob(JobFailed $event): void
    {
        if (! $this->enabled()) {
            return;
        }

        $this->warning('job.failed', [
            'job_class' => $this->safeClass($event->job->resolveName()),
            'exception_class' => $event->exception::class,
        ]);
    }

    /** @return array{route:string,status:int,duration_ms:float} */
    private function requestContext(Request $request, float $durationMs, int $status): array
    {
        return [
            'route' => $this->routeTemplate($request),
            'status' => $status,
            'duration_ms' => max(0, round($durationMs, 1)),
        ];
    }

    private function routeTemplate(Request $request): string
    {
        $route = $request->route();

        return $route instanceof Route ? $this->safeLabel($route->uri()) : 'unmatched';
    }

    private function warning(string $event, array $context): void
    {
        try {
            $this->logs->channel('monitoring')->warning($event, $context);
        } catch (Throwable) {
            // A logging outage must not become an application outage.
        }
    }

    private function enabled(): bool
    {
        return (bool) config('mediahub.monitoring.enabled', true);
    }

    private function slowRequestThreshold(): int
    {
        return max(0, (int) config('mediahub.monitoring.slow_request_ms', 1000));
    }

    private function safeLabel(mixed $value): string
    {
        $label = preg_replace('/[^A-Za-z0-9_.{}:\/-]/', '_', (string) $value) ?: 'unknown';

        return substr($label, 0, 160);
    }

    private function safeClass(mixed $value): string
    {
        $class = (string) $value;

        return class_basename($class !== '' ? $class : 'unknown');
    }
}
