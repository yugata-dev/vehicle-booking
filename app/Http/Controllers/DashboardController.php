<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $monthlyUsage = Booking::query()
            ->selectRaw("DATE_TRUNC('month', start_date) AS month, COUNT(vehicle_id) AS total")
            ->groupByRaw("DATE_TRUNC('month', start_date)")
            ->orderBy('month')
            ->get();

        return view('dashboard', [
            'monthlyUsage' => [
                'labels' => $monthlyUsage->map(fn (Booking $booking): string => Carbon::parse($booking->month)->format('m/Y')),
                'values' => $monthlyUsage->pluck('total'),
            ],
        ]);
    }
}
