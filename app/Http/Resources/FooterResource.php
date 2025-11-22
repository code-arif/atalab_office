<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FooterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'logo' => $this->logo ? asset($this->logo) : null,
            'business_name' => $this->business_name,
            'slogan' => $this->slogan,
            'subscribe_title' => $this->subscribe_title,
            'description' => $this->description,
            'subscribe_description' => $this->subscribe_description,
            'copyright' => $this->copyright,
            'social_links' => $this->formatSocialLinks($this->social_links),
        ];
    }

    /**
     * Convert social links JSON into full URLs for icons.
     */
    private function formatSocialLinks($socialLinks)
    {
        if (!$socialLinks) {
            return [];
        }

        $links = is_string($socialLinks)
            ? json_decode($socialLinks, true)
            : $socialLinks;

        return collect($links)->map(function ($link) {
            return [
                'url' => $link['url'] ?? null,
                'icon' => isset($link['icon']) ? asset($link['icon']) : null,
                'platform' => $link['platform'] ?? null,
            ];
        })->toArray();
    }
}
