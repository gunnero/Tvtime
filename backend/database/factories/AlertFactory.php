<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => 'new-episodes',
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(6),
            'due_text' => 'Available now',
            'payload' => [],
            'unread' => true,
            'read_at' => null,
        ];
    }
}
