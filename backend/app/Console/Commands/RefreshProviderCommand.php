<?php

namespace App\Console\Commands;

use App\Models\PlaybackSource;
use App\Services\ProviderCatalogService;
use Illuminate\Console\Command;
use RuntimeException;

class RefreshProviderCommand extends Command
{
    protected $signature = 'mediahub:refresh-provider {provider_id}';

    protected $description = 'Refresh one private user-owned provider catalog.';

    public function handle(ProviderCatalogService $catalog): int
    {
        $source = PlaybackSource::with('user')->find($this->argument('provider_id'));
        if (! $source || ! $source->user) {
            $this->error('Provider refresh failed: provider was not found.');

            return self::FAILURE;
        }

        try {
            $summary = $catalog->refresh($source->user, $source);
        } catch (RuntimeException $exception) {
            $this->error('Provider refresh failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        foreach ($summary as $key => $value) {
            $this->line($key.': '.(is_bool($value) ? ($value ? 'yes' : 'no') : $value));
        }

        return self::SUCCESS;
    }
}
