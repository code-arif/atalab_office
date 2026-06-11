<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_draw_id',
        'user_id',
        'donation_id',
        'is_rollover',
    ];

    protected $casts = [
        'is_rollover' => 'boolean',
    ];

    public function weeklyDraw(): BelongsTo
    {
        return $this->belongsTo(WeeklyDraw::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
}
