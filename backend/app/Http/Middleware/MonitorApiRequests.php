<?php

namespace App\Http\Middleware;

use App\Services\OperationalMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MonitorApiRequests
{
    public function __construct(
        private readonly OperationalMonitoringService $monitoring,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->withoutAffectingResponse(
                fn () => $this->monitoring->recordException($request, $exception, $this->durationMs($startedAt)),
            );

            throw $exception;
        }

        $this->withoutAffectingResponse(
            fn () => $this->monitoring->recordResponse($request, $response, $this->durationMs($startedAt)),
        );

        return $response;
    }

    private function durationMs(int $startedAt): float
    {
        return round((hrtime(true) - $startedAt) / 1_000_000, 1);
    }

    private function withoutAffectingResponse(Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable) {
            // Monitoring is intentionally best-effort and must never break MediaHub.
        }
    }
}
