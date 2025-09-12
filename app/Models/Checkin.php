<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    protected $fillable = [
        'user_id','date','state','genre','title','start_at','end_at','reason','next_action','duration_minutes'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'date'     => 'date',
    ];

    // Accessor: formatted duration as HH:MM
    public function getDurationHmAttribute(): string
    {
        $m = (int) ($this->duration_minutes ?? 0);
        $h = intdiv($m, 60);
        $mm = $m % 60;
        return sprintf('%02d:%02d', $h, $mm);
    }

    // Mutator: accept "HH:MM" and set duration_minutes
    public function setDurationHmAttribute($value): void
    {
        if (is_string($value) && preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m)) {
            $hours = (int) $m[1];
            $mins  = (int) $m[2];
            $this->attributes['duration_minutes'] = max(0, $hours * 60 + $mins);
        }
    }
}
