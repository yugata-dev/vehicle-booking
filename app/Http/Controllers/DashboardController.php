<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index()
    {
        $totalVehicles = \App\Models\Vehicle::count();
        $approvedBookings = \App\Models\Booking::where('status', 'approved')->count();
        $pendingBookings = \App\Models\Booking::where('status', 'pending')->count();

        // Ambil data 6 bulan terakhir
        $months = [];
        $chartOrang = [];
        $chartBarang = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');

            $chartOrang[] = \App\Models\Booking::where('status', 'approved')
                ->whereHas('vehicle', fn($q) => $q->where('type', 'orang'))
                ->whereYear('start_date', $date->year)
                ->whereMonth('start_date', $date->month)
                ->count();

            $chartBarang[] = \App\Models\Booking::where('status', 'approved')
                ->whereHas('vehicle', fn($q) => $q->where('type', 'barang'))
                ->whereYear('start_date', $date->year)
                ->whereMonth('start_date', $date->month)
                ->count();
        }

        return view('dashboard', compact(
            'totalVehicles',
            'approvedBookings',
            'pendingBookings',
            'months',
            'chartOrang',
            'chartBarang'
        ) + [
            'chartLabels' => $months,
        ]);
    }
}
