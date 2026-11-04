<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();
        $startDate = now()->parse($from)->startOfDay();
        $endDate = now()->parse($to)->endOfDay();
        if ($startDate->gt($endDate)) {
            throw ValidationException::withMessages(['to' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.']);
        }

        $confirmedBookings = Booking::query()
            ->where('status', 'confirmed')
            ->whereNotNull('verified_at')
            ->whereBetween('verified_at', [$startDate, $endDate]);

        $summary = [
            'income' => (clone $confirmedBookings)->sum('amount'),
            'transactions' => (clone $confirmedBookings)->count(),
            'average' => (int) round((clone $confirmedBookings)->avg('amount') ?? 0),
        ];

        $venueSummaries = (clone $confirmedBookings)
            ->join('venues', 'bookings.venue_id', '=', 'venues.id')
            ->selectRaw('venues.name as venue_name, COUNT(bookings.id) as transaction_count, SUM(bookings.amount) as income')
            ->groupBy('venues.id', 'venues.name')
            ->orderByDesc('income')
            ->get();

        $transactions = (clone $confirmedBookings)
            ->with(['court:id,name', 'venue:id,name'])
            ->orderByDesc('verified_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.financial-reports.index', compact(
            'from',
            'to',
            'summary',
            'venueSummaries',
            'transactions',
        ));
    }
}
