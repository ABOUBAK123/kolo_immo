<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favoriteProperties()
            ->with(['photos'])
            ->where('status', 'active')
            ->latest('favorites.created_at')
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'title'           => $p->title,
                'city'            => $p->city,
                'price_per_night' => $p->price_per_night,
                'cover_photo_url' => $p->cover_photo_url,
                'rating_avg'      => $p->rating_avg,
                'type'            => $p->type,
                'price_at_save'   => $p->pivot->price_at_save,
                'price_dropped'   => $p->pivot->price_at_save && $p->price_per_night < $p->pivot->price_at_save,
            ]);

        return response()->json(['success' => true, 'data' => $favorites]);
    }

    public function toggle(Request $request, Property $property)
    {
        $user = $request->user();

        $existing = Favorite::where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $faved = false;
        } else {
            Favorite::create([
                'user_id'       => $user->id,
                'property_id'   => $property->id,
                'price_at_save' => $property->price_per_night,
            ]);
            $faved = true;
        }

        return response()->json([
            'success' => true,
            'faved'   => $faved,
            'message' => $faved ? 'Bien ajouté aux favoris.' : 'Bien retiré des favoris.',
        ]);
    }
}
