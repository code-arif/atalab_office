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
            'participants' => number_format($this->participants),
            'pool' => number_format($this->total_pool, 2),
            'recipients' => number_format($this->recipients),
            'odds' => "1 in " . number_format($this->odds_denominator),
            'net_per_recipient' => number_format($this->net_per_recipient, 2),
        ];
    }
}
