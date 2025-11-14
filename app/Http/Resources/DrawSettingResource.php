<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DrawSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'participants' => [
                'total' => $this->participants,
                'formatted' => number_format($this->participants)
            ],
            'pool' => [
                'total' => (float) $this->total_pool,
                'formatted' => '$' . number_format($this->total_pool, 2),
                'currency' => 'USD'
            ],
            'recipients' => [
                'count' => $this->recipients,
                'formatted' => number_format($this->recipients)
            ],
            'odds' => [
                'numerator' => $this->odds_numerator,
                'denominator' => $this->odds_denominator,
                'ratio' => "{$this->odds_numerator}:{$this->odds_denominator}",
                'formatted' => "1 in " . number_format($this->odds_denominator),
                'percentage' => round(($this->odds_numerator / $this->odds_denominator) * 100, 4) . '%'
            ],
            'net_per_recipient' => [
                'amount' => (float) $this->net_per_recipient,
                'formatted' => '$' . number_format($this->net_per_recipient, 2)
            ],
            'calculations' => [
                'total_distributed' => (float) ($this->net_per_recipient * $this->recipients),
                'total_distributed_formatted' => '$' . number_format($this->net_per_recipient * $this->recipients, 2),
                'pool_remaining' => (float) ($this->total_pool - ($this->net_per_recipient * $this->recipients)),
                'pool_remaining_formatted' => '$' . number_format($this->total_pool - ($this->net_per_recipient * $this->recipients), 2)
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s')
        ];
    }
}
