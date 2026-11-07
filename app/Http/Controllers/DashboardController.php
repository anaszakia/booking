<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = User::query()->findOrFail(session('user_id'));
        $isAdmin = $user->hasRole('superadmin');
        $canViewBookings = $isAdmin || $user->hasRole('staff');
        $today = now()->toDateString();

        $todayBookings = $canViewBookings
            ? Booking::query()
                ->with(['court:id,name', 'venue:id,name'])
                ->blocksSlot()
                ->whereDate('booking_date', $today)
                ->orderBy('booking_time')
                ->get()
            : collect();

        $todayCourtSummary = $todayBookings
            ->groupBy('court_id')
            ->map(fn ($bookings) => [
                'court' => $bookings->first()->court->name,
                'venue' => $bookings->first()->venue->name,
                'count' => $bookings->count(),
                'hours' => $bookings->sum('duration_hours'),
                'amount' => $bookings->sum('amount'),
            ])
            ->values();

        $pendingProofCount = $isAdmin
            ? Booking::query()->where('status', 'pending_verification')->whereNotNull('payment_proof_path')->count()
            : 0;
        $pendingProofs = $isAdmin
            ? Booking::query()
                ->with(['court:id,name', 'venue:id,name'])
                ->where('status', 'pending_verification')
                ->whereNotNull('payment_proof_path')
                ->orderBy('payment_uploaded_at')
                ->limit(8)
                ->get()
            : collect();

        return view('dashboard', compact(
            'user',
            'isAdmin',
            'canViewBookings',
            'today',
            'todayBookings',
            'todayCourtSummary',
            'pendingProofCount',
            'pendingProofs',
        ));
    }
}
