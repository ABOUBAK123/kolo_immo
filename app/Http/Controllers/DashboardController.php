<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // ── Analytics: 12 months revenue ────────────────────────────────────
        $revenueChart = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            // Include wallet payments too
            $amount = Booking::where('owner_id', $user->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereIn('payment_status', ['paid', 'escrowed', 'released', 'wallet'])
                ->whereYear('confirmed_at', $month->year)
                ->whereMonth('confirmed_at', $month->month)
                ->sum('total_amount');

            $revenueChart[] = [
                'month'  => $month->locale('fr')->isoFormat('MMM YY'),
                'amount' => (float) $amount,
            ];
        }

        // ── Comparison: this month vs last month ──────────────────────────────
        $thisMonth  = Booking::where('owner_id', $user->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'escrowed', 'released', 'wallet'])
            ->whereYear('confirmed_at', now()->year)
            ->whereMonth('confirmed_at', now()->month)
            ->sum('total_amount');

        $lastMonth  = Booking::where('owner_id', $user->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['paid', 'escrowed', 'released', 'wallet'])
            ->whereYear('confirmed_at', now()->subMonth()->year)
            ->whereMonth('confirmed_at', now()->subMonth()->month)
            ->sum('total_amount');

        $revenueGrowth = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);

        // ── Occupancy per property ─────────────────────────────────────────────
        $daysInMonth = now()->daysInMonth;
        $occupancyPerProperty = $user->properties()
            ->where('status', 'active')
            ->with('photos')
            ->get()
            ->map(function ($p) use ($daysInMonth) {
                $bookedNights = Booking::where('property_id', $p->id)
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->whereYear('check_in', now()->year)
                    ->whereMonth('check_in', now()->month)
                    ->sum('nights');
                return [
                    'id'    => $p->id,
                    'title' => \Illuminate\Support\Str::limit($p->title, 25),
                    'rate'  => min(100, round(($bookedNights / $daysInMonth) * 100)),
                    'nights'=> (int) $bookedNights,
                    'photo' => $p->cover_photo_url,
                ];
            });

        // ── Booking status breakdown (pie chart) ──────────────────────────────
        $bookingBreakdown = Booking::where('owner_id', $user->id)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $analyticsData = [
            'revenue_chart'         => $revenueChart,
            'this_month_revenue'    => (float) $thisMonth,
            'last_month_revenue'    => (float) $lastMonth,
            'revenue_growth'        => $revenueGrowth,
            'occupancy_per_property'=> $occupancyPerProperty,
            'booking_breakdown'     => $bookingBreakdown,
        ];

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
            'myProperties',
            'analyticsData'
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
     * Export owner analytics as CSV.
     */
    public function exportCsv(Request $request)
    {
        $user  = Auth::user();
        $year  = (int) $request->get('year', now()->year);

        $bookings = Booking::where('owner_id', $user->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereYear('confirmed_at', $year)
            ->with(['property', 'tenant'])
            ->orderBy('confirmed_at')
            ->get();

        $filename = "kolo_immo_rapport_{$year}.csv";
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($file, ['Référence', 'Bien', 'Locataire', 'Arrivée', 'Départ', 'Nuits', 'Montant (FCFA)', 'Statut'], ';');

            foreach ($bookings as $b) {
                fputcsv($file, [
                    $b->reference,
                    $b->property?->title ?? '—',
                    $b->tenant?->name ?? '—',
                    $b->check_in->format('d/m/Y'),
                    $b->check_out->format('d/m/Y'),
                    $b->nights,
                    number_format($b->total_amount, 0, ',', ' '),
                    $b->status,
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export owner analytics as printable PDF (HTML view for browser print).
     */
    public function exportPdf(Request $request)
    {
        $user  = Auth::user();
        $year  = (int) $request->get('year', now()->year);

        $bookings = Booking::where('owner_id', $user->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereYear('confirmed_at', $year)
            ->with(['property', 'tenant'])
            ->orderBy('confirmed_at')
            ->get();

        $totalRevenue = $bookings->sum('total_amount');

        // Monthly breakdown
        $byMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $byMonth[$m] = [
                'label'  => now()->month($m)->locale('fr')->isoFormat('MMMM'),
                'count'  => $bookings->filter(fn($b) => $b->confirmed_at?->month === $m)->count(),
                'amount' => $bookings->filter(fn($b) => $b->confirmed_at?->month === $m)->sum('total_amount'),
            ];
        }

        return view('owner.analytics-pdf', compact('bookings', 'totalRevenue', 'byMonth', 'year', 'user'));
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
