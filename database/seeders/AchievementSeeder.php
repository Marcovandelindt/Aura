<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            ['key' => 'eerste_stap', 'name' => 'Eerste stap', 'description' => 'Eerste taak afgerond', 'icon' => '👣', 'xp_bonus' => 25],
            ['key' => 'op_stoom', 'name' => 'Op stoom', 'description' => '3 dagen streak', 'icon' => '🔥', 'xp_bonus' => 50],
            ['key' => 'doorzetter', 'name' => 'Doorzetter', 'description' => '7 dagen streak', 'icon' => '💪', 'xp_bonus' => 100],
            ['key' => 'huisbaas', 'name' => 'Huisbaas', 'description' => '10 huistaken afgerond', 'icon' => '🏠', 'xp_bonus' => 75],
            ['key' => 'wandelaar', 'name' => 'Wandelaar', 'description' => '10 bewegingstaken afgerond', 'icon' => '🚶', 'xp_bonus' => 75],
            ['key' => 'level_5', 'name' => 'Level 5', 'description' => 'Level 5 bereikt', 'icon' => '⭐', 'xp_bonus' => 150],
            ['key' => 'honderd', 'name' => 'Honderd!', 'description' => '100 taken afgerond', 'icon' => '🏆', 'xp_bonus' => 200],
        ];

        foreach ($achievements as $data) {
            Achievement::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
