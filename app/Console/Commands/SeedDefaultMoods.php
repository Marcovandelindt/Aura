<?php

namespace App\Console\Commands;

use App\Models\Mood;
use Illuminate\Console\Command;

class SeedDefaultMoods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moods:seed-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default moods for track tagging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultMoods = [
            [
                'name' => 'Emotional',
                'color' => '#dc2626',
                'icon' => 'fas fa-heart',
                'description' => 'Deep, emotional, and touching music'
            ],
            [
                'name' => 'Christmas',
                'color' => '#16a34a',
                'icon' => 'fas fa-tree',
                'description' => 'Festive holiday and Christmas music'
            ],
            [
                'name' => 'Future House',
                'color' => '#7c3aed',
                'icon' => 'fas fa-music',
                'description' => 'Electronic dance music with future house style'
            ],
            [
                'name' => 'Energetic',
                'color' => '#f59e0b',
                'icon' => 'fas fa-bolt',
                'description' => 'High energy, upbeat music'
            ],
            [
                'name' => 'Chill',
                'color' => '#06b6d4',
                'icon' => 'fas fa-leaf',
                'description' => 'Relaxed, laid-back vibes'
            ],
            [
                'name' => 'Workout',
                'color' => '#ef4444',
                'icon' => 'fas fa-dumbbell',
                'description' => 'Perfect for exercise and fitness'
            ],
            [
                'name' => 'Focus',
                'color' => '#8b5cf6',
                'icon' => 'fas fa-brain',
                'description' => 'Music for concentration and productivity'
            ],
            [
                'name' => 'Nostalgic',
                'color' => '#f97316',
                'icon' => 'fas fa-clock',
                'description' => 'Brings back memories and nostalgia'
            ],
            [
                'name' => 'Party',
                'color' => '#ec4899',
                'icon' => 'fas fa-glass-cheers',
                'description' => 'Perfect for parties and celebrations'
            ],
            [
                'name' => 'Sad',
                'color' => '#6b7280',
                'icon' => 'fas fa-cloud-rain',
                'description' => 'Melancholic and somber music'
            ],
            [
                'name' => 'Romantic',
                'color' => '#db2777',
                'icon' => 'fas fa-heart',
                'description' => 'Love songs and romantic music'
            ],
            [
                'name' => 'Study',
                'color' => '#059669',
                'icon' => 'fas fa-book',
                'description' => 'Background music for studying'
            ]
        ];

        $this->info('Seeding default moods...');

        foreach ($defaultMoods as $moodData) {
            $existing = Mood::where('name', $moodData['name'])->first();
            
            if (!$existing) {
                Mood::create($moodData);
                $this->line("✅ Created: {$moodData['name']}");
            } else {
                $this->line("⏭️  Exists: {$moodData['name']}");
            }
        }

        $this->info('✨ Default moods seeding completed!');

        return 0;
    }
}
