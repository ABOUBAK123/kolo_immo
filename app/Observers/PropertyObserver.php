<?php

namespace App\Observers;

use App\Mail\PriceDropMail;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PropertyObserver
{
    public function updating(Property $property): void
    {
        // Detect price drop before the update is saved
        if (!$property->isDirty('price_per_night')) {
            return;
        }

        $oldPrice = (float) $property->getOriginal('price_per_night');
        $newPrice = (float) $property->price_per_night;

        if ($newPrice >= $oldPrice) {
            return;
        }

        // Notify users who have this property in their favorites
        $favorites = Favorite::where('property_id', $property->id)
            ->with('user')
            ->get();

        foreach ($favorites as $fav) {
            // Update price_at_save so the badge shows on next visit
            $fav->update(['price_at_save' => $oldPrice]);

            if ($fav->user && $fav->user->email) {
                try {
                    Mail::to($fav->user->email)
                        ->queue(new PriceDropMail($fav->user, $property, $oldPrice, $newPrice));
                } catch (\Throwable $e) {
                    Log::warning('[PriceDrop] Mail failed for user ' . $fav->user->id . ': ' . $e->getMessage());
                }
            }
        }
    }
}
