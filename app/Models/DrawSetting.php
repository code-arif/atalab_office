<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrawSetting extends Model
{
    protected $fillable = [
        'participants',
        'total_pool',
        'recipients',
        'odds_numerator',
        'odds_denominator',
        'net_per_recipient',
    ];


    /**
     * Get formatted odds ratio
     */
    public function getOddsRatioAttribute(): string
    {
        return "{$this->odds_numerator}:{$this->odds_denominator}";
    }

    /**
     * Get odds percentage
     */
    public function getOddsPercentageAttribute(): float
    {
        return round(($this->odds_numerator / $this->odds_denominator) * 100, 4);
    }

    /**
     * Get total amount to be distributed
     */
    public function getTotalDistributedAttribute(): float
    {
        return $this->net_per_recipient * $this->recipients;
    }

    /**
     * Get remaining pool amount
     */
    public function getPoolRemainingAttribute(): float
    {
        return $this->total_pool - $this->total_distributed;
    }
}
