<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StabilizationSummaryService
{
    /** @return array<string, int> */
    public function forDays(int $days): array
    {
        $days = max(1, min(90, $days));
        $cutoff = CarbonImmutable::now()->subDays($days);
        $counts = [
            'api_slow' => 0,
            'api_5xx' => 0,
            'api_exceptions' => 0,
            'failed_jobs_logged' => 0,
            'failed_jobs_database' => 0,
        ];

        foreach ($this->logFiles() as $path) {
            $handle = @fopen($path, 'rb');
            if (! $handle) {
                continue;
            }

            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if (! is_array($entry) || ! $this->withinWindow($entry['datetime'] ?? null, $cutoff)) {
                    continue;
                }

                match ($entry['message'] ?? null) {
                    'api.slow' => $counts['api_slow']++,
                    'api.5xx' => $counts['api_5xx']++,
                    'api.exception' => $counts['api_exceptions']++,
                    'job.failed' => $counts['failed_jobs_logged']++,
                    default => null,
                };
            }

            fclose($handle);
        }

        try {
            if (Schema::hasTable('failed_jobs')) {
                $counts['failed_jobs_database'] = DB::table('failed_jobs')
                    ->where('failed_at', '>=', $cutoff)
                    ->count();
            }
        } catch (Throwable) {
            $counts['failed_jobs_database'] = 0;
        }

        return $counts;
    }

    /** @return list<string> */
    private function logFiles(): array
    {
        $paths = glob((string) config('mediahub.monitoring.log_glob')) ?: [];
        sort($paths);

        return array_values(array_filter($paths, 'is_file'));
    }

    private function withinWindow(mixed $value, CarbonImmutable $cutoff): bool
    {
        try {
            return filled($value) && CarbonImmutable::parse((string) $value)->greaterThanOrEqualTo($cutoff);
        } catch (Throwable) {
            return false;
        }
    }
}
