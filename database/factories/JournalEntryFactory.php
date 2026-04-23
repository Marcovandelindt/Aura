<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => '<p>'.implode('</p><p>', $this->faker->paragraphs(3)).'</p>',
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'rating' => $this->faker->numberBetween(1, 10),
            'mood_id' => null,
            'location' => $this->faker->optional(0.5)->city(),
            'word_count' => $this->faker->numberBetween(50, 800),
        ];
    }
}
