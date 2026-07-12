<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 40)->nullable()->unique()->after('name');
            $table->string('display_name', 80)->nullable()->after('username');
            $table->text('bio')->nullable()->after('display_name');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->boolean('public_profile_enabled')->default(false)->after('avatar_path');
            $table->string('profile_visibility', 16)->default('private')->index()->after('public_profile_enabled');
            $table->string('profile_slug', 60)->nullable()->unique()->after('profile_visibility');
            $table->string('country', 80)->nullable()->after('profile_slug');
            $table->json('favorite_genres')->nullable()->after('country');
            $table->json('favorite_movie_ids')->nullable()->after('favorite_genres');
            $table->json('favorite_show_ids')->nullable()->after('favorite_movie_ids');
            $table->json('featured_list_ids')->nullable()->after('favorite_show_ids');
            $table->boolean('show_statistics')->default(false)->after('featured_list_ids');
            $table->boolean('show_favorite_movies')->default(false)->after('show_statistics');
            $table->boolean('show_favorite_shows')->default(false)->after('show_favorite_movies');
            $table->boolean('show_public_lists')->default(false)->after('show_favorite_shows');
            $table->boolean('show_recent_activity')->default(false)->after('show_public_lists');
            $table->boolean('allow_friend_requests')->default(false)->after('show_recent_activity');
            $table->boolean('allow_profile_sharing')->default(false)->after('allow_friend_requests');
            $table->boolean('allow_search_discovery')->default(false)->after('allow_profile_sharing');
            $table->timestamp('joined_at')->nullable()->after('last_login_at');
            $table->timestamp('last_active_at')->nullable()->index()->after('joined_at');
        });

        $usedUsernames = [];
        $usedSlugs = array_fill_keys([
            'admin', 'api', 'assets', 'discover', 'friends', 'help', 'invite', 'invites',
            'login', 'logout', 'mediahub', 'movies', 'privacy', 'profile', 'settings',
            'shows', 'static', 'support', 'u',
        ], true);
        DB::table('users')->orderBy('id')->get()->each(function (object $user) use (&$usedSlugs, &$usedUsernames): void {
            $usernameBase = Str::limit(Str::lower(Str::slug((string) $user->name, '_')), 30, '') ?: 'member_'.$user->id;
            $slugBase = Str::limit(Str::lower(Str::slug((string) $user->name)), 45, '') ?: 'member-'.$user->id;
            $username = $this->uniqueValue($usernameBase, $usedUsernames, '_'.$user->id);
            $slug = $this->uniqueValue($slugBase, $usedSlugs, '-'.$user->id);

            DB::table('users')->where('id', $user->id)->update([
                'username' => $username,
                'display_name' => $user->name,
                'profile_slug' => $slug,
                'joined_at' => $user->created_at,
            ]);
        });

        Schema::create('friendships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requester_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('addressee_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pair_key', 64)->unique();
            $table->string('status', 16)->default('pending')->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            $table->index(['requester_user_id', 'status']);
            $table->index(['addressee_user_id', 'status']);
        });

        Schema::create('friend_invites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inviter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('status', 16)->default('pending')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['inviter_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friend_invites');
        Schema::dropIfExists('friendships');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'username',
                'display_name',
                'bio',
                'avatar_path',
                'public_profile_enabled',
                'profile_visibility',
                'profile_slug',
                'country',
                'favorite_genres',
                'favorite_movie_ids',
                'favorite_show_ids',
                'featured_list_ids',
                'show_statistics',
                'show_favorite_movies',
                'show_favorite_shows',
                'show_public_lists',
                'show_recent_activity',
                'allow_friend_requests',
                'allow_profile_sharing',
                'allow_search_discovery',
                'joined_at',
                'last_active_at',
            ]);
        });
    }

    /** @param array<string, true> $used */
    private function uniqueValue(string $base, array &$used, string $suffix): string
    {
        $candidate = $base;
        if (isset($used[$candidate])) {
            $candidate = $base.$suffix;
        }

        $counter = 2;
        while (isset($used[$candidate])) {
            $candidate = $base.$suffix.'_'.$counter;
            $counter++;
        }

        $used[$candidate] = true;

        return $candidate;
    }
};
