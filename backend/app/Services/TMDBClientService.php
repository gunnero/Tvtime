<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TMDBClientService
{
    public function enabled(): bool
    {
        return (bool) config('tmdb.enabled') && filled(config('tmdb.api_key'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function searchMovie(string $title, ?int $year = null): ?array
    {
        return $this->get('/search/movie', array_filter([
            'query' => $title,
            'year' => $year,
            'include_adult' => false,
            'language' => 'en-US',
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function searchShow(string $title, ?int $year = null): ?array
    {
        return $this->get('/search/tv', array_filter([
            'query' => $title,
            'first_air_date_year' => $year,
            'include_adult' => false,
            'language' => 'en-US',
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMovie(int $tmdbId): ?array
    {
        return $this->get('/movie/'.$tmdbId, [
            'language' => 'en-US',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getShow(int $tmdbId): ?array
    {
        return $this->get('/tv/'.$tmdbId, [
            'append_to_response' => 'external_ids',
            'language' => 'en-US',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSeason(int $showTmdbId, int $seasonNumber): ?array
    {
        return $this->get('/tv/'.$showTmdbId.'/season/'.$seasonNumber, [
            'language' => 'en-US',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getEpisode(int $showTmdbId, int $seasonNumber, int $episodeNumber): ?array
    {
        return $this->get('/tv/'.$showTmdbId.'/season/'.$seasonNumber.'/episode/'.$episodeNumber, [
            'append_to_response' => 'external_ids',
            'language' => 'en-US',
        ]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    private function get(string $path, array $params = []): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        $cacheKey = 'tmdb:'.sha1($path.'|'.json_encode($params, JSON_THROW_ON_ERROR));
        $ttl = max(60, (int) config('tmdb.cache_ttl', 86400));

        return Cache::remember($cacheKey, $ttl, function () use ($params, $path): ?array {
            try {
                $response = Http::timeout(max(1, (int) config('tmdb.timeout', 20)))
                    ->acceptJson()
                    ->get(rtrim((string) config('tmdb.base_url'), '/').$path, [
                        ...$params,
                        'api_key' => config('tmdb.api_key'),
                    ]);
            } catch (Throwable) {
                Log::warning('TMDB request failed.', ['endpoint' => $path]);

                return null;
            }

            if (! $response->ok()) {
                Log::warning('TMDB request returned non-success status.', [
                    'endpoint' => $path,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $payload = $response->json();

            return is_array($payload) ? $payload : null;
        });
    }
}
