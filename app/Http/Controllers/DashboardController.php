<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Payment;
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

        return view('owner.dashboard', compact(
            'monthlyRevenue',
            'totalRevenue',
            'totalNightsThisMonth',
            'totalProperties',
            'activeProperties',
            'pendingBookings',
            'pendingCount',
            'recentConversations',
            'revenueChart',
            'upcomingCheckIns'
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
}
