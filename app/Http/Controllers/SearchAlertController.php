<?php

namespace App\Http\Controllers;

use App\Models\SearchAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchAlertController extends Controller
{
    public function index()
    {
        $alerts = Auth::user()->searchAlerts()->latest()->get();
        return view('search-alerts.index', compact('alerts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'in:daily,weekly'],
            'city'      => ['nullable', 'string', 'max:100'],
            'type'      => ['nullable', 'string', 'max:50'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'guests'    => ['nullable', 'integer', 'min:1'],
        ]);

        $user = Auth::user();

        if ($user->searchAlerts()->count() >= 5) {
            return back()->with('error', 'Vous avez atteint la limite de 5 alertes. Supprimez-en une pour en créer une nouvelle.');
        }

        $filters = array_filter([
            'city'      => $data['city'] ?? null,
            'type'      => $data['type'] ?? null,
            'price_min' => $data['price_min'] ?? null,
            'price_max' => $data['price_max'] ?? null,
            'guests'    => $data['guests'] ?? null,
        ]);

        $user->searchAlerts()->create([
            'name'      => $data['name'],
            'filters'   => $filters,
            'frequency' => $data['frequency'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Alerte créée ! Vous serez notifié par email quand de nouveaux biens correspondent.');
    }

    public function toggle(SearchAlert $alert)
    {
        $this->authorize_alert($alert);
        $alert->update(['is_active' => !$alert->is_active]);
        $label = $alert->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "Alerte {$label}.");
    }

    public function destroy(SearchAlert $alert)
    {
        $this->authorize_alert($alert);
        $alert->delete();
        return back()->with('success', 'Alerte supprimée.');
    }

    private function authorize_alert(SearchAlert $alert): void
    {
        if ($alert->user_id !== Auth::id()) abort(403);
    }
}
