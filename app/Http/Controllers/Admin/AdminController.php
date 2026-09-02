<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\KycDocument;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin dashboard with platform-wide stats.
     */
    public function dashboard()
    {
        $stats = [
            'users_count'       => User::count(),
            'active_properties' => Property::where('status', 'active')->count(),
            'bookings_count'    => Booking::count(),
            'platform_revenue'  => Booking::where('payment_status', 'released')->sum('platform_commission'),
            'pending_kyc'       => KycDocument::where('status', 'pending')->count(),
            'open_disputes'     => Booking::where('status', 'disputed')->count(),
        ];

        $bookingsByStatus = Booking::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentBookings = Booking::with(['property', 'tenant'])
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'bookingsByStatus', 'recentBookings', 'recentUsers'));
    }

    /**
     * List all users with filters.
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('kyc')) {
            $query->where('kyc_status', $request->kyc);
        }

        if ($request->filled('is_banned')) {
            $query->where('is_banned', (bool) $request->is_banned);
        }

        if ($request->filled('activation') && $request->activation === 'pending') {
            $query->where('is_active', false)->where('is_banned', false);
        }

        $users = $query->withCount(['properties', 'bookingsAsTenant', 'bookingsAsOwner'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show a user's details.
     */
    public function showUser(User $user)
    {
        $user->load(['properties', 'kycDocuments', 'bookingsAsTenant', 'bookingsAsOwner']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Toggle user ban status.
     */
    public function toggleBan(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de bannir un administrateur.');
        }

        $user->update(['is_banned' => !$user->is_banned]);
        $label = $user->is_banned ? 'banni' : 'débanni';

        return back()->with('success', "Utilisateur {$label}.");
    }

    /**
     * Toggle user active status (activate / deactivate account).
     */
    public function toggleActive(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de modifier le statut d\'un administrateur.');
        }

        $activating = !$user->is_active;
        $updates    = ['is_active' => $activating];

        // Generate the agent's referral code the first time their account is activated.
        if ($activating && $user->isAgent() && empty($user->agent_code)) {
            $updates['agent_code'] = User::generateAgentCode();
        }

        $user->update($updates);
        $label = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Compte utilisateur {$label}.");
    }

    /**
     * Show KYC verification form.
     */
    public function showKyc(KycDocument $kycDocument)
    {
        $kycDocument->load('user');
        return view('admin.kyc.show', compact('kycDocument'));
    }

    /**
     * Approve or reject a KYC document.
     */
    public function verifyKyc(Request $request, KycDocument $kycDocument)
    {
        $request->validate([
            'action'           => ['required', 'in:approve,reject'],
            'rejection_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:500'],
        ], [
            'action.required'           => 'Veuillez choisir une action.',
            'rejection_reason.required_if' => 'Le motif du rejet est obligatoire.',
        ]);

        if ($request->action === 'approve') {
            $kycDocument->update([
                'status'      => 'approved',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Update user KYC status and trust score
            $kycDocument->user->update([
                'kyc_status'  => 'verified',
                'trust_score' => min(100, ($kycDocument->user->trust_score ?? 50) + 30),
            ]);

            return back()->with('success', 'Document KYC approuvé. L\'utilisateur est maintenant vérifié.');
        }

        $reason = $request->rejection_reason;
        if ($request->filled('rejection_details')) {
            $reason .= ' — ' . trim($request->rejection_details);
        }

        $kycDocument->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'verified_at'      => now(),
            'verified_by'      => auth()->id(),
        ]);

        $kycDocument->user->update(['kyc_status' => 'rejected']);

        return back()->with('success', 'Document KYC rejeté. L\'utilisateur sera notifié.');
    }

    /**
     * List pending KYC documents.
     */
    public function kycList(Request $request)
    {
        $status = $request->get('status', 'pending');

        $kycDocuments = KycDocument::when($status !== '', fn($q) => $q->where('status', $status))
            ->with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending'  => KycDocument::where('status', 'pending')->count(),
            'approved' => KycDocument::where('status', 'approved')->count(),
            'rejected' => KycDocument::where('status', 'rejected')->count(),
        ];

        return view('admin.kyc.index', compact('kycDocuments', 'stats', 'status'));
    }

    /**
     * Admin property listing / moderation.
     */
    public function properties(Request $request)
    {
        $query = Property::with(['owner'])->withCount(['bookings', 'reviews']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('verification')) {
            $query->where('verification_status', $request->verification);
        }

        $properties = $query->latest()->paginate(20)->withQueryString();

        return view('admin.properties.index', compact('properties'));
    }

    /**
     * Toggle property featured status.
     */
    public function toggleFeatured(Property $property)
    {
        $property->update(['is_featured' => !$property->is_featured]);
        $label = $property->is_featured ? 'mis en avant' : 'retiré des mises en avant';
        return back()->with('success', "Logement {$label}.");
    }

    /**
     * Suspend a property.
     */
    public function suspendProperty(Property $property)
    {
        $property->update(['status' => 'suspended']);
        return back()->with('success', 'Logement suspendu.');
    }

    /**
     * Mark property as under review.
     */
    public function underReviewProperty(Property $property)
    {
        $property->update(['verification_status' => 'under_review']);
        return back()->with('success', 'Logement marqué "en cours d\'examen".');
    }

    /**
     * Verify (approve) a property.
     */
    public function verifyProperty(Request $request, Property $property)
    {
        $property->update([
            'verification_status' => 'verified',
            'verification_notes'  => $request->input('notes'),
            'verified_by'         => auth()->id(),
            'verified_at'         => now(),
        ]);
        return back()->with('success', 'Logement vérifié et approuvé.');
    }

    /**
     * Reject a property with notes.
     */
    public function rejectProperty(Request $request, Property $property)
    {
        $request->validate(['notes' => 'required|string|max:500']);
        $property->update([
            'verification_status' => 'rejected',
            'verification_notes'  => $request->notes,
            'verified_by'         => auth()->id(),
            'verified_at'         => now(),
        ]);
        return back()->with('success', 'Logement rejeté. Le propriétaire sera informé.');
    }

    /**
     * Toggle property active/inactive (admin override).
     */
    public function togglePropertyStatus(Property $property)
    {
        if ($property->status === 'suspended') {
            $property->update(['status' => 'inactive']);
            return back()->with('success', 'Logement réactivé (inactif — le propriétaire peut l\'activer).');
        }

        $newStatus = $property->status === 'active' ? 'inactive' : 'active';
        $property->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activé' : 'désactivé';
        return back()->with('success', "Logement {$label} par l'administrateur.");
    }

    /**
     * Admin booking listing.
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['property', 'tenant', 'owner', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('reference', 'like', "%{$s}%");
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * List disputed bookings.
     */
    public function disputes()
    {
        $disputes = Booking::where('status', 'disputed')
            ->with(['property', 'tenant', 'owner', 'payment'])
            ->latest()
            ->paginate(15);

        return view('admin.disputes.index', compact('disputes'));
    }

    /**
     * Admin review moderation.
     */
    public function reviews(Request $request)
    {
        $query = Review::with(['reviewer', 'reviewee', 'property', 'booking']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->get('flagged') === '1') {
            $query->where('is_flagged', true);
        }

        if ($request->filled('rating')) {
            $query->where('rating_overall', '<=', (int) $request->rating);
        }

        $reviews     = $query->latest()->paginate(20)->withQueryString();
        $flaggedCount = Review::where('is_flagged', true)->count();

        return view('admin.reviews.index', compact('reviews', 'flaggedCount'));
    }

    /**
     * Unflag a review (admin).
     */
    public function unflagReview(Review $review)
    {
        $review->update(['is_flagged' => false, 'flag_reason' => null]);
        return back()->with('success', 'Signalement retiré.');
    }

    /**
     * Delete a review (admin).
     */
    public function deleteReview(Review $review)
    {
        $propertyId = $review->property_id;
        $type       = $review->type;
        $review->delete();

        // Recalculate property rating if it was a property review
        if ($type === 'tenant_to_property' && $propertyId) {
            $stats = Review::where('property_id', $propertyId)->where('type', 'tenant_to_property')
                ->selectRaw('AVG(rating_overall) as avg_rating, COUNT(*) as count')->first();
            Property::where('id', $propertyId)->update([
                'rating_avg'   => round($stats->avg_rating ?? 0, 1),
                'rating_count' => $stats->count ?? 0,
            ]);
        }

        return back()->with('success', 'Avis supprimé.');
    }

    /**
     * Revenue reports.
     */
    public function reports(Request $request)
    {
        $period = $request->get('period', 'month');

        $query = Payment::where('status', 'success')->where('type', 'payment');

        if ($period === 'month') {
            $query->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('paid_at', now()->year);
        }

        $totalRevenue      = $query->sum('amount');
        $platformRevenue   = Booking::whereHas('payments', fn($q) => $q->where('status', 'success'))
            ->sum('platform_commission');
        $totalTransactions = $query->count();
        $avgTransAmount    = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Revenue by method
        $revenueByMethod = Payment::where('status', 'success')
            ->where('type', 'payment')
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();

        // Daily revenue for the current month
        $dailyRevenue = Payment::where('status', 'success')
            ->where('type', 'payment')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.reports.index', compact(
            'totalRevenue',
            'platformRevenue',
            'totalTransactions',
            'avgTransAmount',
            'revenueByMethod',
            'dailyRevenue',
            'period'
        ));
    }
}
