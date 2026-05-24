<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Owner dashboard with revenue stats, occupancy, pending bookings, recent messages.
     */
    public function ownerDashboard()
    {
        $user = Auth::user();

        if (!$user->isOwner()) {
            return redirect()->route('dashboard');
        }

        // Revenue stats (current month)
        $monthlyRevenue = Payment::whereHas('booking', fn($q) => $q->where('owner_id', $user->id))
            ->where('status', 'success')
            ->where('type', 'payment')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Total all-time revenue
        $totalRevenue = Payment::whereHas('booking', fn($q) => $q->where('owner_id', $user->id))
            ->where('status', 'success')
            ->where('type', 'payment')
            ->sum('amount');

        // Occupancy: count confirmed nights this month
        $confirmedBookings = $user->bookingsAsOwner()
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();

        $totalNightsThisMonth = $confirmedBookings->filter(function ($b) {
            return $b->check_in->month === now()->month
                || $b->check_out->month === now()->month;
        })->sum('nights');

        // Total properties
        $totalProperties = $user->properties()->count();
        $activeProperties = $user->properties()->where('status', 'active')->count();

        // Pending bookings
        $pendingBookings = $user->bookingsAsOwner()
            ->pending()
            ->with(['property', 'tenant'])
            ->latest()
            ->take(5)
            ->get();

        $pendingCount = $user->bookingsAsOwner()->pending()->count();

        // Recent messages
        $recentConversations = Conversation::where('owner_id', $user->id)
            ->with(['tenant', 'property', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->take(5)
            ->get();

        // Monthly revenue chart data (last 6 months)
        $revenueChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Payment::whereHas('booking', fn($q) => $q->where('owner_id', $user->id))
                ->where('status', 'success')
                ->where('type', 'payment')
                ->whereMonth('paid_at', $month->month)
                ->whereYear('paid_at', $month->year)
                ->sum('amount');

            $revenueChart[] = [
                'month'  => $month->locale('fr')->isoFormat('MMM YY'),
                'amount' => $amount,
            ];
        }

        // Upcoming check-ins
        $upcomingCheckIns = $user->bookingsAsOwner()
            ->where('status', 'confirmed')
            ->where('check_in', '>=', now()->toDateString())
            ->with(['tenant', 'property'])
            ->orderBy('check_in')
            ->take(5)
            ->get();

        // Recent bookings (for dashboard table)
        $recentBookings = $user->bookingsAsOwner()
            ->with(['tenant', 'property'])
            ->latest()
            ->take(8)
            ->get();

        // Owner's properties with photos
        $myProperties = $user->properties()
            ->with('photos')
            ->latest()
            ->take(5)
            ->get();

        // Average rating across all properties
        $avgRating = Review::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->avg('rating_overall') ?? 0;

        // Occupancy rate (booked nights this month / days in month)
        $daysInMonth = now()->daysInMonth;
        $occupancyRate = $daysInMonth > 0
            ? round(($totalNightsThisMonth / ($daysInMonth * max($activeProperties, 1))) * 100)
            : 0;

        $stats = [
            'monthly_revenue'  => $monthlyRevenue,
            'occupancy_rate'   => min($occupancyRate, 100),
            'pending_bookings' => $pendingCount,
            'average_rating'   => round($avgRating, 1),
        ];

        return view('owner.dashboard', compact(
            'stats',
            'monthlyRevenue',
            'totalRevenue',
            'totalNightsThisMonth',
            'totalProperties',
            'activeProperties',
            'pendingBookings',
            'pendingCount',
            'recentConversations',
            'revenueChart',
            'upcomingCheckIns',
            'recentBookings',
            'myProperties'
        ));
    }

    /**
     * Tenant dashboard with upcoming trips, recent activity, trust score.
     */
    public function tenantDashboard()
    {
        $user = Auth::user();

        // Upcoming trips
        $upcomingBookings = $user->bookingsAsTenant()
            ->upcoming()
            ->with(['property.photos', 'payment'])
            ->orderBy('check_in')
            ->take(5)
            ->get();

        // Past trips
        $pastBookings = $user->bookingsAsTenant()
            ->completed()
            ->with(['property.photos', 'reviews'])
            ->latest()
            ->take(3)
            ->get();

        // Active bookings count
        $activeBookingsCount = $user->bookingsAsTenant()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        // Total trips count
        $totalTrips = $user->bookingsAsTenant()->count();

        // Total spent
        $totalSpent = Payment::where('user_id', $user->id)
            ->where('status', 'success')
            ->where('type', 'payment')
            ->sum('amount');

        // Unread messages
        $unreadMessages = 0;
        Conversation::where('tenant_id', $user->id)
            ->with('messages')
            ->get()
            ->each(function ($conv) use ($user, &$unreadMessages) {
                $unreadMessages += $conv->unreadCountFor($user);
            });

        // Recent activity
        $recentActivity = $user->bookingsAsTenant()
            ->with(['property', 'property.photos'])
            ->latest()
            ->take(5)
            ->get();

        // Trust score breakdown
        $trustScore      = $user->trust_score ?? 50;
        $kycVerified     = $user->isKycVerified();
        $phoneVerified   = $user->phone_verified_at !== null;
        $reviewsCount    = $user->reviewsReceived()->count();

        return view('dashboard.tenant', compact(
            'upcomingBookings',
            'pastBookings',
            'activeBookingsCount',
            'totalTrips',
            'totalSpent',
            'unreadMessages',
            'recentActivity',
            'trustScore',
            'kycVerified',
            'phoneVerified',
            'reviewsCount'
        ));
    }

    /**
     * Owner commission statement: 3% of all confirmed/completed booking totals.
     */
    public function ownerCommissions(Request $request)
    {
        $user = Auth::user();

        $properties = Property::where('owner_id', $user->id)->pluck('id');

        // All confirmed/completed bookings for owner's properties
        $bookings = Booking::whereIn('property_id', $properties)
            ->whereIn('status', ['confirmed', 'completed'])
            ->with(['property', 'tenant', 'payment'])
            ->orderByDesc('created_at')
            ->get();

        $commissionRate = 0.03;

        $rows = $bookings->map(function (Booking $b) use ($commissionRate) {
            return [
                'id'          => $b->id,
                'reference'   => $b->reference,
                'property'    => $b->property?->title ?? '—',
                'tenant'      => $b->tenant?->name ?? '—',
                'check_in'    => $b->check_in,
                'check_out'   => $b->check_out,
                'nights'      => $b->nights,
                'total'       => $b->total_amount,
                'commission'  => round($b->total_amount * $commissionRate),
                'status'      => $b->status,
                'paid_at'     => $b->payment?->paid_at,
            ];
        });

        // Period filter (optional)
        $period = $request->get('period', 'all');
        if ($period === 'month') {
            $rows = $rows->filter(fn($r) => $r['check_in'] >= now()->startOfMonth()->toDateString());
        } elseif ($period === 'year') {
            $rows = $rows->filter(fn($r) => $r['check_in'] >= now()->startOfYear()->toDateString());
        }

        $totalRevenue    = $rows->sum('total');
        $totalCommission = $rows->sum('commission');
        $monthlyRows     = $bookings->filter(fn($b) => $b->check_in >= now()->startOfMonth()->toDateString());
        $monthlyRevenue  = $monthlyRows->sum('total_amount');
        $monthlyCommission = round($monthlyRevenue * $commissionRate);

        return view('owner.commissions', compact(
            'rows',
            'period',
            'totalRevenue',
            'totalCommission',
            'monthlyRevenue',
            'monthlyCommission',
            'commissionRate'
        ));
    }
}
