<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Whether to include full content and SEO meta fields.
     */
    protected bool $isDetail = false;

    /**
     * Mark this resource as a detail view (includes content + SEO meta).
     */
    public function forDetail(): static
    {
        $this->isDetail = true;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'excerpt'        => $this->excerpt,
            'featured_image' => $this->featured_image,
            'reading_time'   => $this->reading_time,
            'status'         => $this->status,
            'published_at'   => $this->published_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'author'         => $this->when($this->relationLoaded('author') && $this->author, function () {
                return [
                    'id'   => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),
        ];

        // Include full content and SEO metadata only for the detail endpoint
        if ($this->isDetail) {
            $data['content']           = $this->content;
            $data['meta_title']        = $this->meta_title;
            $data['meta_description']  = $this->meta_description;
            $data['meta_keywords']     = $this->meta_keywords;
        }

        return $data;
    }
}
