<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * List the authenticated user's favorites.
     */
    public function index()
    {
        $favorites = Auth::user()
            ->favoriteProperties()
            ->with(['photos', 'owner'])
            ->where('status', 'active')
            ->latest('favorites.created_at')
            ->paginate(12);

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Toggle a property favorite for the current user.
     * Returns JSON for AJAX calls, redirect otherwise.
     */
    public function toggle(Request $request, Property $property)
    {
        $user = Auth::user();

        $existing = Favorite::where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $faved = false;
        } else {
            Favorite::create([
                'user_id'      => $user->id,
                'property_id'  => $property->id,
                'price_at_save'=> $property->price_per_night,
            ]);
            $faved = true;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'faved'         => $faved,
                'favorites_count' => $property->favorites()->count(),
            ]);
        }

        return back()->with($faved ? 'success' : 'info',
            $faved ? 'Bien ajouté à vos favoris.' : 'Bien retiré de vos favoris.'
        );
    }
}
