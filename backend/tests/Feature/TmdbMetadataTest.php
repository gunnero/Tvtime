<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Episode;
use App\Models\MediaLink;
use App\Models\Movie;
use App\Models\MovieWatch;
use App\Models\PlaybackSource;
use App\Models\PlaybackSourceItem;
use App\Models\Show;
use App\Models\User;
use App\Services\DashboardPayloadService;
use App\Services\MediaMetadataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TmdbMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_tmdb_disabled_does_not_enrich_movie_or_fail(): void
    {
        Config::set('tmdb.enabled', false);

        $movie = Movie::create([
            'user_id' => $this->member()->id,
            'title' => 'Heat',
            'runtime' => 170,
        ]);

        $summary = app(MediaMetadataService::class)->enrichMovie($movie);

        $this->assertSame(1, $summary['skipped']);
        $this->assertSame(0, $summary['enriched']);
        $this->assertNull($movie->refresh()->tmdb_id);
    }

    public function test_failed_tmdb_request_does_not_break_app_or_log_api_key(): void
    {
        Config::set('tmdb.enabled', true);
        Config::set('tmdb.api_key', 'super-secret-tmdb-key');
        Log::spy();
        Http::fake([
            'api.themoviedb.org/*' => Http::response(['status_message' => 'bad gateway'], 502),
        ]);

        $movie = Movie::create([
            'user_id' => $this->member()->id,
            'title' => 'Heat',
        ]);

        $summary = app(MediaMetadataService::class)->enrichMovie($movie);

        $this->assertSame(1, $summary['failed']);
        $this->assertNull($movie->refresh()->tmdb_id);
        Log::shouldNotHaveReceived('warning', function (string $message, array $context = []): bool {
            return str_contains(json_encode([$message, $context], JSON_THROW_ON_ERROR), 'super-secret-tmdb-key');
        });
    }

    public function test_movie_enrichment_stores_canonical_metadata_additively(): void
    {
        Config::set('tmdb.enabled', true);
        Config::set('tmdb.api_key', 'test-key');
        $user = $this->member();
        $movie = Movie::create([
            'user_id' => $user->id,
            'title' => 'Heat',
            'runtime' => 0,
            'poster_url' => '/assets/generated/movie-poster-1.png',
        ]);

        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [[
                    'id' => 949,
                    'title' => 'Heat',
                    'original_title' => 'Heat',
                    'release_date' => '1995-12-15',
                    'overview' => 'A meticulous crime saga.',
                    'poster_path' => '/heat-poster.jpg',
                    'backdrop_path' => '/heat-backdrop.jpg',
                    'vote_average' => 7.9,
                ]],
            ]),
            'api.themoviedb.org/3/movie/949*' => Http::response([
                'id' => 949,
                'imdb_id' => 'tt0113277',
                'title' => 'Heat',
                'original_title' => 'Heat',
                'overview' => 'A meticulous crime saga.',
                'poster_path' => '/heat-poster.jpg',
                'backdrop_path' => '/heat-backdrop.jpg',
                'release_date' => '1995-12-15',
                'genres' => [['id' => 80, 'name' => 'Crime'], ['id' => 18, 'name' => 'Drama']],
                'runtime' => 170,
                'status' => 'Released',
                'vote_average' => 7.9,
            ]),
        ]);

        $summary = app(MediaMetadataService::class)->enrichMovie($movie);
        $movie->refresh();

        $this->assertSame(1, $summary['matched']);
        $this->assertSame(1, $summary['enriched']);
        $this->assertSame(949, $movie->tmdb_id);
        $this->assertSame('tt0113277', $movie->imdb_id);
        $this->assertSame('Heat', $movie->original_title);
        $this->assertSame('A meticulous crime saga.', $movie->overview);
        $this->assertSame('/heat-poster.jpg', $movie->poster_path);
        $this->assertSame('/heat-backdrop.jpg', $movie->backdrop_path);
        $this->assertSame('1995-12-15', $movie->release_date?->toDateString());
        $this->assertSame('Crime', $movie->genres[0]['name']);
        $this->assertSame(170, $movie->runtime);
        $this->assertSame('Released', $movie->status);
        $this->assertSame(7.9, $movie->vote_average);
        $this->assertSame('/assets/generated/movie-poster-1.png', $movie->poster_url);
        $this->assertSame('tmdb', $movie->metadata['match']['source']);
        $this->assertNotNull($movie->metadata_refreshed_at);
    }

    public function test_show_and_episode_enrichment_store_metadata_when_show_has_tmdb_id(): void
    {
        Config::set('tmdb.enabled', true);
        Config::set('tmdb.api_key', 'test-key');
        $user = $this->member();
        $show = Show::create([
            'user_id' => $user->id,
            'title' => 'Severance',
            'runtime' => 0,
        ]);
        $episode = Episode::create([
            'user_id' => $user->id,
            'show_id' => $show->id,
            'season_number' => 1,
            'episode_number' => 1,
            'title' => 'Good News About Hell',
        ]);

        Http::fake([
            'api.themoviedb.org/3/search/tv*' => Http::response([
                'results' => [[
                    'id' => 95396,
                    'name' => 'Severance',
                    'original_name' => 'Severance',
                    'first_air_date' => '2022-02-18',
                    'overview' => 'Work-life balance, surgically enforced.',
                    'poster_path' => '/severance-poster.jpg',
                    'backdrop_path' => '/severance-backdrop.jpg',
                    'vote_average' => 8.4,
                ]],
            ]),
            'api.themoviedb.org/3/tv/95396/season/1/episode/1*' => Http::response([
                'id' => 1832406,
                'name' => 'Good News About Hell',
                'overview' => 'Mark reports to work.',
                'still_path' => '/severance-episode.jpg',
                'air_date' => '2022-02-18',
                'runtime' => 57,
                'vote_average' => 8.1,
                'external_ids' => ['imdb_id' => 'tt15047476', 'tvdb_id' => 8304110],
            ]),
            'api.themoviedb.org/3/tv/95396*' => Http::response([
                'id' => 95396,
                'name' => 'Severance',
                'original_name' => 'Severance',
                'overview' => 'Work-life balance, surgically enforced.',
                'poster_path' => '/severance-poster.jpg',
                'backdrop_path' => '/severance-backdrop.jpg',
                'first_air_date' => '2022-02-18',
                'genres' => [['id' => 18, 'name' => 'Drama']],
                'episode_run_time' => [50],
                'status' => 'Returning Series',
                'vote_average' => 8.4,
                'external_ids' => ['imdb_id' => 'tt11280740', 'tvdb_id' => 371980],
            ]),
        ]);

        $summary = app(MediaMetadataService::class)->enrichShow($show, enrichEpisodes: true);

        $this->assertSame(2, $summary['enriched']);
        $this->assertSame(95396, $show->refresh()->tmdb_id);
        $this->assertSame('tt11280740', $show->imdb_id);
        $this->assertSame('371980', $show->tvdb_id);
        $this->assertSame('/severance-poster.jpg', $show->poster_path);
        $this->assertSame('2022-02-18', $show->first_air_date?->toDateString());
        $this->assertSame(50, $show->runtime);
        $this->assertSame(1832406, $episode->refresh()->tmdb_id);
        $this->assertSame('tt15047476', $episode->imdb_id);
        $this->assertSame('8304110', $episode->tvdb_id);
        $this->assertSame('/severance-episode.jpg', $episode->poster_path);
        $this->assertSame(57, $episode->runtime);
    }

    public function test_user_enrichment_is_scoped_and_dashboard_hides_provider_urls(): void
    {
        Config::set('tmdb.enabled', true);
        Config::set('tmdb.api_key', 'test-key');
        $user = $this->member();
        $otherUser = $this->member('other@example.test');
        $movie = Movie::create(['user_id' => $user->id, 'title' => 'Heat']);
        $otherMovie = Movie::create(['user_id' => $otherUser->id, 'title' => 'Heat']);
        MovieWatch::create([
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'watched_at' => now(),
            'runtime' => 170,
            'source' => 'manual',
        ]);
        $source = PlaybackSource::create([
            'user_id' => $user->id,
            'name' => 'Private source',
            'provider_type' => 'manual',
            'status' => 'active',
        ]);
        $item = PlaybackSourceItem::create([
            'user_id' => $user->id,
            'playback_source_id' => $source->id,
            'external_id' => 'private-heat',
            'kind' => 'movie',
            'title' => 'Heat private',
            'status' => 'available',
            'stream_url' => 'mediahub-private-stream-ref',
            'stream_url_hash' => hash('sha256', 'mediahub-private-stream-ref'),
        ]);
        MediaLink::create([
            'user_id' => $user->id,
            'playback_source_item_id' => $item->id,
            'movie_id' => $movie->id,
            'linked_at' => now(),
        ]);

        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response(['results' => [['id' => 949, 'title' => 'Heat']]]),
            'api.themoviedb.org/3/movie/949*' => Http::response([
                'id' => 949,
                'title' => 'Heat',
                'poster_path' => '/heat-poster.jpg',
                'backdrop_path' => '/heat-backdrop.jpg',
                'release_date' => '1995-12-15',
                'genres' => [['id' => 80, 'name' => 'Crime']],
                'runtime' => 170,
                'status' => 'Released',
            ]),
        ]);

        $summary = app(MediaMetadataService::class)->enrichUser($user);

        $this->assertSame(1, $summary['enriched']);
        $this->assertSame(949, $movie->refresh()->tmdb_id);
        $this->assertNull($otherMovie->refresh()->tmdb_id);

        $payload = app(DashboardPayloadService::class)->forUser($user);
        $encoded = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->assertSame('https://image.tmdb.org/t/p/w500/heat-poster.jpg', $payload['recentlyWatched'][0]['poster']);
        $this->assertStringContainsString('Crime', $encoded);
        $this->assertStringNotContainsString('stream_url', $encoded);
        $this->assertStringNotContainsString('playbackUrl', $encoded);
        $this->assertStringNotContainsString('mediahub-private-stream-ref', $encoded);
    }

    private function member(string $email = 'member@example.test'): User
    {
        return User::factory()->create([
            'email' => $email,
            'role' => UserRole::Member,
            'status' => UserStatus::Active,
        ]);
    }
}
