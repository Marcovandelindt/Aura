<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JournalTag>
 */
class JournalTagFactory extends Factory
{
    public function definition(): array
    {
        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6'];

        return [
            'name' => $this->faker->word(),
            'color' => $this->faker->randomElement($colors),
        ];
    }
}
