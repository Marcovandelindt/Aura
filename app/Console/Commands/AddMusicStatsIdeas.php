<?php

namespace App\Console\Commands;

use App\Models\Idea;
use Illuminate\Console\Command;

class AddMusicStatsIdeas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'music:add-future-stats-ideas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add future music statistics ideas to the ideas list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ideas = [
            [
                'title' => 'Weekpatronen Analysis', 
                'description' => 'Volledige week analyse van luistergedrag - welke dagen luister je het meest?', 
                'priority' => 'medium', 
                'category' => 'feature'
            ],
            [
                'title' => 'Artist Loyalty Tracking', 
                'description' => 'Consistentie in luistergedrag - hoe lang blijf je bij dezelfde artiesten hangen?', 
                'priority' => 'medium', 
                'category' => 'feature'
            ],
            [
                'title' => 'Meaningful Listening Streaks', 
                'description' => 'Langste periodes van aaneengesloten muziek luisteren', 
                'priority' => 'medium', 
                'category' => 'feature'
            ],
            [
                'title' => 'Seasonal Music Analysis', 
                'description' => 'Welke maanden ben je het meest muzikaal actief? Winter vs zomer vibes analyse', 
                'priority' => 'low', 
                'category' => 'feature'
            ],
            [
                'title' => 'Era Analysis - Music Decades', 
                'description' => 'Uit welke decennia komt je muziek het meest? 80s, 90s, 2000s, etc.', 
                'priority' => 'low', 
                'category' => 'feature'
            ],
            [
                'title' => 'Genre Diversity Spreading', 
                'description' => 'Hoe divers is je muzieksmaak? Analyse van genre spreiding', 
                'priority' => 'low', 
                'category' => 'feature'
            ],
            [
                'title' => 'Artist Collaboration Network', 
                'description' => 'Welke artiesten werken vaak samen in jouw top tracks? Netwerk visualisatie', 
                'priority' => 'low', 
                'category' => 'feature'
            ],
            [
                'title' => 'Spotify Audio Features Integration', 
                'description' => 'Energy levels, danceability, valence - integratie van Spotify audio features voor diepere analyse', 
                'priority' => 'low', 
                'category' => 'feature'
            ]
        ];

        $this->info('Adding future music statistics ideas...');

        foreach ($ideas as $ideaData) {
            // Check if idea already exists
            $existing = Idea::where('title', $ideaData['title'])->first();
            
            if (!$existing) {
                Idea::create($ideaData);
                $this->line("✅ Added: {$ideaData['title']}");
            } else {
                $this->line("⏭️  Skipped (exists): {$ideaData['title']}");
            }
        }

        $this->info('✨ Done adding future music stats ideas!');
        
        return 0;
    }
}
