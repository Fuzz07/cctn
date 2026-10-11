<?php

namespace App\Services;

use App\Models\Promo;

class PromoPricing
{
    public function snapshot(float $originalAmount): array
    {
        return $this->snapshotForPromo($originalAmount, $this->activePromo());
    }

    public function activePromo(): ?Promo
    {
        return Promo::active()
            ->orderByDesc('discount_percent')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();
    }

    public function snapshotForPromo(float $originalAmount, ?Promo $promo): array
    {
        $originalAmount = round(max(0, $originalAmount), 2);
        $discountPercent = $promo ? (float) $promo->discount_percent : 0;
        $discountAmount = round($originalAmount * $discountPercent / 100, 2);

        return [
            'promo_id' => $promo?->id,
            'promo_title' => $promo?->title,
            'promo_description' => $promo?->description,
            'promo_discount_percent' => $discountPercent,
            'promo_discount_amount' => $discountAmount,
            'original_amount' => $originalAmount,
            'amount_due' => round($originalAmount - $discountAmount, 2),
        ];
    }
}
