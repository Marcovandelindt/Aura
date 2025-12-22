<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Action',
            'Adventure',
            'RPG',
            'Strategy',
            'Simulation',
            'Sports',
            'Racing',
            'Puzzle',
            'Shooter',
            'Platformer',
            'Fighting',
            'Horror',
            'Survival',
            'Sandbox',
            'Open World',
            'Stealth',
            'Battle Royale',
            'Roguelike',
            'Metroidvania',
            'Souls-like',
            'Visual Novel',
            'Rhythm',
            'Card Game',
            'Tower Defense',
            'City Builder',
            'Management',
            'Party',
            'Couch Co-op',
            'MMO',
            'MOBA',
            'Indie',
            'Retro',
        ];

        foreach ($genres as $name) {
            Genre::firstOrCreate(['name' => $name]);
        }
    }
}
