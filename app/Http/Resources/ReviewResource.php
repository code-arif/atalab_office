<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'author_name'  => $this->author_name,
            'author_avatar' => $this->author_avatar ? asset($this->author_avatar) : null,
            'rating'       => $this->rating,
            'review_text'  => $this->review_text,
            'week_label'   => $this->week_label,
            'created_at'   => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
