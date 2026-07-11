<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Episode;
use App\Models\EpisodeWatch;
use App\Models\Show;
use App\Models\User;
use App\Services\ImportRelationshipRepairService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportRelationshipRepairTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_reports_deterministic_relationships_without_writing(): void
    {
        [$user, $show, $episode, $watch] = $this->brokenRelationship();

        $this->artisan('mediahub:repair-import-relationships', ['user_id' => $user->id, '--dry-run' => true])
            ->expectsOutput('mode: dry-run')
            ->expectsOutput('episode links repairable: 1')
            ->assertSuccessful();

        $this->assertNull($episode->fresh()->show_id);
        $this->assertSame($show->id, $watch->fresh()->show_id);
    }

    public function test_apply_repairs_same_user_relationships_and_is_idempotent(): void
    {
        [$user, $show, $episode] = $this->brokenRelationship();
        $service = app(ImportRelationshipRepairService::class);

        $first = $service->apply($user);
        $this->assertSame(1, $first['episode_links_repaired']);
        $this->assertSame($show->id, $episode->fresh()->show_id);
        $this->assertSame(1, $show->fresh()->seen_episodes);
        $this->assertSame(1, $show->fresh()->aired_episodes);

        $second = $service->apply($user);
        $this->assertSame(0, $second['episode_links_repaired']);
        $this->assertSame(0, $second['show_counters_repaired']);
    }

    public function test_ambiguous_and_cross_user_relationships_remain_unresolved(): void
    {
        $user = $this->member('owner@example.test');
        $other = $this->member('other@example.test');
        $otherShow = Show::create(['user_id' => $other->id, 'title' => 'Other']);
        $episode = Episode::create(['user_id' => $user->id, 'show_id' => null, 'external_source' => 'tvtime', 'external_id' => 'orphan', 'season_number' => 1, 'episode_number' => 1]);
        EpisodeWatch::create(['user_id' => $user->id, 'show_id' => $otherShow->id, 'episode_id' => $episode->id, 'watched_at' => now(), 'source' => 'tvtime-import']);

        $summary = app(ImportRelationshipRepairService::class)->apply($user);
        $this->assertSame(0, $summary['episode_links_repaired']);
        $this->assertNull($episode->fresh()->show_id);
    }

    public function test_aggregate_only_show_gaps_are_reported_without_fabricating_episodes(): void
    {
        $user = $this->member();
        Show::create(['user_id' => $user->id, 'title' => 'Aggregate Only', 'seen_episodes' => 12, 'aired_episodes' => 12]);

        $summary = app(ImportRelationshipRepairService::class)->apply($user);
        $this->assertSame(1, $summary['aggregate_only_show_gaps']);
        $this->assertSame(0, Episode::forUser($user)->count());
    }

    public function test_show_counter_repair_ignores_cross_user_relationship_rows(): void
    {
        $user = $this->member();
        $other = $this->member('cross-user@example.test');
        $show = Show::create(['user_id' => $user->id, 'title' => 'Owned Show']);
        $otherEpisode = Episode::create([
            'user_id' => $other->id,
            'show_id' => $show->id,
            'season_number' => 1,
            'episode_number' => 1,
        ]);
        EpisodeWatch::create([
            'user_id' => $other->id,
            'show_id' => $show->id,
            'episode_id' => $otherEpisode->id,
            'watched_at' => now(),
            'runtime' => 45,
            'source' => 'manual',
        ]);

        $summary = app(ImportRelationshipRepairService::class)->apply($user);

        $this->assertSame(0, $summary['show_counters_repaired']);
        $this->assertSame(0, $show->fresh()->seen_episodes);
        $this->assertSame(0, $show->fresh()->aired_episodes);
        $this->assertNull($show->fresh()->latest_seen_at);
    }

    private function brokenRelationship(): array
    {
        $user = $this->member();
        $show = Show::create(['user_id' => $user->id, 'title' => 'Repair Me']);
        $episode = Episode::create(['user_id' => $user->id, 'show_id' => null, 'external_source' => 'tvtime', 'external_id' => 'episode-1', 'season_number' => 1, 'episode_number' => 1]);
        $watch = EpisodeWatch::create(['user_id' => $user->id, 'show_id' => $show->id, 'episode_id' => $episode->id, 'watched_at' => now(), 'runtime' => 45, 'source' => 'tvtime-import']);

        return [$user, $show, $episode, $watch];
    }

    private function member(string $email = 'member@example.test'): User
    {
        return User::factory()->create(['email' => $email, 'role' => UserRole::Member, 'status' => UserStatus::Active]);
    }
}
