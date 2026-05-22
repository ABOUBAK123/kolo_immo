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

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        if ($request->filled('is_banned')) {
            $query->where('is_banned', (bool) $request->is_banned);
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

        $kycDocument->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
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

        $documents = KycDocument::where('status', $status)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.kyc.index', compact('documents', 'status'));
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
