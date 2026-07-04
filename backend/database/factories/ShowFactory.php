<?php

namespace Database\Factories;

use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Show>
 */
class ShowFactory extends Factory
{
    protected $model = Show::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'external_source' => 'manual',
            'external_id' => fake()->uuid(),
            'title' => fake()->words(3, true),
            'poster_url' => null,
            'fanart_url' => null,
            'followed' => true,
            'seen_episodes' => 0,
            'aired_episodes' => 0,
            'runtime' => 0,
            'latest_seen_at' => null,
        ];
    }
}
