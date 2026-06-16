<?php

namespace App\Http\Resources\V2\Donation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    /**
     * Whether to include the has_duplicate_card check.
     * Must be set explicitly via the constructor or additional() to avoid N+1 queries.
     */
    protected bool $checkDuplicate = false;

    /**
     * Set whether to perform duplicate card lookup.
     */
    public function withDuplicateCheck(bool $check): static
    {
        $this->checkDuplicate = $check;
        return $this;
    }

    /**
     * Transform the donation resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'donation_id'           => $this->donation_id,
            'user_id'               => $this->user_id,
            'week_id'               => $this->week_id,
            'amount'                => (float) $this->amount,
            'processing_fee'        => (float) $this->processing_fee,
            'total_amount'          => (float) $this->total_amount,
            'is_cover'              => (bool) $this->is_cover,
            'payment_type'          => $this->payment_type,
            'stripe_payment_status' => $this->stripe_payment_status,
            'is_eligible_for_draw'  => (bool) $this->is_eligible_for_draw,
            'card_fingerprint'      => $this->card_fingerprint
                ? substr($this->card_fingerprint, 0, 8) . '...'
                : null,
            'has_duplicate_card'    => $this->when($this->checkDuplicate && $this->card_fingerprint, function () {
                return \App\Models\Donation::where('card_fingerprint', $this->card_fingerprint)
                    ->where('week_id', $this->week_id)
                    ->where('id', '!=', $this->id)
                    ->where('stripe_payment_status', 'completed')
                    ->exists();
            }),
            'donated_at'            => $this->donated_at?->toIso8601String(),
            'created_at'            => $this->created_at?->toIso8601String(),

            // Relationships
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id'       => $this->user->id,
                    'name'     => $this->user->name,
                    'email'    => $this->user->email,
                    'donor_id' => $this->user->donor_id,
                ];
            }),

            'week' => $this->when($this->relationLoaded('weeklyDraw'), function () {
                return [
                    'id'          => $this->weeklyDraw->id,
                    'week_number' => $this->weeklyDraw->week_number,
                    'status'      => $this->weeklyDraw->status,
                ];
            }),
        ];
    }
}
