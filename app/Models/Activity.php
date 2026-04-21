<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'strava_id',
        'name',
        'type',
        'sport_type',
        'distance',
        'moving_time',
        'elapsed_time',
        'total_elevation_gain',
        'start_date',
        'start_date_local',
        'timezone',
        'average_speed',
        'max_speed',
        'average_heartrate',
        'max_heartrate',
        'calories',
        'description',
        'map_polyline',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'start_date_local' => 'datetime',
            'distance' => 'float',
            'total_elevation_gain' => 'float',
            'average_speed' => 'float',
            'max_speed' => 'float',
            'average_heartrate' => 'float',
            'calories' => 'float',
        ];
    }

    public function getDistanceInKmAttribute(): float
    {
        return round($this->distance / 1000, 2);
    }

    public function getMovingTimePrettyAttribute(): string
    {
        $hours = intdiv($this->moving_time, 3600);
        $minutes = intdiv($this->moving_time % 3600, 60);
        $seconds = $this->moving_time % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
