<?php

namespace Tests\Feature;

use App\Services\OperationalMonitoringService;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Routing\Route as RouteObject;
use Illuminate\Support\Facades\Route;
use Mockery;
use Monolog\Formatter\JsonFormatter;
use Tests\TestCase;

class StabilizationMonitoringTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryLogs = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryLogs as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_api_monitoring_records_only_route_template_and_safe_metrics(): void
    {
        $logPath = $this->configureMonitoringLog();
        Route::middleware('api')->get('/api/v1/stabilization/{item}', fn () => response()->json(['ready' => false], 503));

        $this->getJson('/api/v1/stabilization/private-media-title?query=private-search')
            ->assertStatus(503);

        $entry = $this->lastJsonLine($logPath);
        $encoded = json_encode($entry, JSON_THROW_ON_ERROR);

        $this->assertSame('api.5xx', $entry['message']);
        $this->assertSame('api/v1/stabilization/{item}', $entry['context']['route']);
        $this->assertSame(503, $entry['context']['status']);
        $this->assertArrayHasKey('duration_ms', $entry['context']);
        $this->assertEqualsCanonicalizing(
            ['route', 'status', 'duration_ms'],
            array_keys($entry['context']),
        );
        $this->assertStringNotContainsString('private-media-title', $encoded);
        $this->assertStringNotContainsString('private-search', $encoded);
        $this->assertArrayNotHasKey('url', $entry['context']);
        $this->assertArrayNotHasKey('user_id', $entry['context']);
        $this->assertArrayNotHasKey('ip', $entry['context']);
    }

    public function test_monitoring_failure_never_breaks_api_response(): void
    {
        config()->set('mediahub.monitoring.enabled', true);
        config()->set('mediahub.monitoring.slow_request_ms', 0);

        $monitoring = Mockery::mock(OperationalMonitoringService::class);
        $monitoring->shouldReceive('recordResponse')->andThrow(new \RuntimeException('monitor unavailable'));
        $this->app->instance(OperationalMonitoringService::class, $monitoring);

        $this->getJson('/api/v1/status')->assertOk();
    }

    public function test_exception_and_failed_job_monitoring_exclude_messages_and_payloads(): void
    {
        $logPath = $this->configureMonitoringLog();
        $request = Request::create('/api/v1/library/movies/private-title?query=private-search', 'GET');
        $route = new RouteObject(['GET'], 'api/v1/library/movies/{movie}', fn () => null);
        $request->setRouteResolver(fn () => $route);

        app(OperationalMonitoringService::class)->recordException(
            $request,
            new \RuntimeException('private media title and provider password'),
            25.4,
        );

        $job = Mockery::mock(Job::class);
        $job->shouldReceive('resolveName')->once()->andReturn('App\\Jobs\\RefreshMetadata');
        app(OperationalMonitoringService::class)->recordFailedJob(new JobFailed(
            'database',
            $job,
            new \RuntimeException('private provider credential'),
        ));

        $encoded = (string) file_get_contents($logPath);
        $entries = array_map(
            fn (string $line): array => json_decode($line, true, flags: JSON_THROW_ON_ERROR),
            array_values(array_filter(explode("\n", trim($encoded)))),
        );

        $this->assertStringContainsString('api.exception', $encoded);
        $this->assertStringContainsString('job.failed', $encoded);
        $this->assertStringContainsString('RuntimeException', $encoded);
        $this->assertStringNotContainsString('private-title', $encoded);
        $this->assertStringNotContainsString('private-search', $encoded);
        $this->assertStringNotContainsString('provider password', $encoded);
        $this->assertStringNotContainsString('provider credential', $encoded);
        $this->assertEqualsCanonicalizing(
            ['route', 'status', 'duration_ms', 'exception_class'],
            array_keys($entries[0]['context']),
        );
        $this->assertEqualsCanonicalizing(
            ['job_class', 'exception_class'],
            array_keys($entries[1]['context']),
        );
    }

    public function test_weekly_summary_command_reports_safe_aggregate_counts(): void
    {
        $logPath = tempnam(sys_get_temp_dir(), 'mediahub-monitoring-');
        $this->temporaryLogs[] = $logPath;
        config()->set('mediahub.monitoring.log_glob', $logPath);

        file_put_contents($logPath, implode("\n", [
            json_encode(['message' => 'api.slow', 'datetime' => now()->toIso8601String(), 'context' => ['route' => 'api/v1/statistics']], JSON_THROW_ON_ERROR),
            json_encode(['message' => 'api.5xx', 'datetime' => now()->toIso8601String(), 'context' => ['route' => 'api/v1/discover/search']], JSON_THROW_ON_ERROR),
            json_encode(['message' => 'job.failed', 'datetime' => now()->toIso8601String(), 'context' => ['queue' => 'default']], JSON_THROW_ON_ERROR),
        ])."\n");

        $this->artisan('mediahub:stabilization-summary', ['--days' => 7])
            ->expectsOutput('window_days: 7')
            ->expectsOutput('api_slow: 1')
            ->expectsOutput('api_5xx: 1')
            ->expectsOutput('api_exceptions: 0')
            ->expectsOutput('failed_jobs_logged: 1')
            ->assertSuccessful();
    }

    private function configureMonitoringLog(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mediahub-monitoring-');
        $this->temporaryLogs[] = $path;

        config()->set('mediahub.monitoring.enabled', true);
        config()->set('mediahub.monitoring.slow_request_ms', 0);
        config()->set('logging.channels.monitoring', [
            'driver' => 'single',
            'path' => $path,
            'level' => 'warning',
            'formatter' => JsonFormatter::class,
        ]);
        app(LogManager::class)->forgetChannel('monitoring');

        return $path;
    }

    /** @return array<string, mixed> */
    private function lastJsonLine(string $path): array
    {
        $lines = array_values(array_filter(explode("\n", trim((string) file_get_contents($path)))));

        return json_decode((string) end($lines), true, flags: JSON_THROW_ON_ERROR);
    }
}
