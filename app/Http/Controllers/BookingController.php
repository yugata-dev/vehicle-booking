<?php

namespace App\Http\Controllers;

use App\Exports\BookingsExport;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', Rule::when($request->filled('from'), ['after_or_equal:from'])],
        ]);

        $bookings = Booking::query()
            ->with(['vehicle', 'driver', 'creator'])
            ->when($request->user()->role !== 'admin', fn(Builder $query) => $query->where('created_by', $request->user()->id))
            ->when($filters['status'] ?? null, fn(Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['vehicle_id'] ?? null, fn(Builder $query, int $vehicleId) => $query->where('vehicle_id', $vehicleId))
            ->when($filters['from'] ?? null, fn(Builder $query, string $from) => $query->whereDate('end_date', '>=', $from))
            ->when($filters['to'] ?? null, fn(Builder $query, string $to) => $query->whereDate('start_date', '<=', $to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', [
            'bookings' => $bookings,
            'vehicles' => Vehicle::query()->orderBy('plate_number')->get(),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', Rule::when($request->filled('start_date'), ['after_or_equal:start_date'])],
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'export',
            'entity' => 'booking_export',
            'entity_id' => null,
            'description' => sprintf(
                'Ekspor pemesanan untuk rentang tanggal %s sampai %s.',
                $validated['start_date'] ?? 'tanpa batas awal',
                $validated['end_date'] ?? 'tanpa batas akhir',
            ),
        ]);

        return Excel::download(
            new BookingsExport(
                $request->user(),
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null,
            ),
            'bookings.xlsx',
        );
    }

    public function create(): View
    {
        return view('bookings.create', [
            'vehicles' => Vehicle::query()
                ->where('status', '!=', 'service')
                ->orderBy('plate_number')
                ->get(),
            'hasServiceVehicles' => Vehicle::query()->where('status', 'service')->exists(),
            'drivers' => Driver::query()->orderBy('name')->get(),
            'approvers' => User::query()->where('role', 'approver')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $approverRule = Rule::exists('users', 'id')->where('role', 'approver');

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'requester_name' => ['required', 'string', 'max:255'],
            'start_date' => ['bail', 'required', 'date_format:Y-m-d\\TH:i'],
            'end_date' => ['bail', 'required', 'date_format:Y-m-d\\TH:i', 'after:start_date'],
            'purpose' => ['required', 'string', 'max:5000'],
            'approver_level_1' => [
                'required',
                'integer',
                $approverRule,
                'different:approver_level_2',
            ],
            'approver_level_2' => ['required', 'integer', $approverRule],
        ], [
            'start_date.required' => 'Waktu mulai wajib diisi.',
            'start_date.date_format' => 'Waktu mulai harus berupa tanggal dan waktu yang valid.',
            'end_date.required' => 'Waktu selesai wajib diisi.',
            'end_date.date_format' => 'Waktu selesai harus berupa tanggal dan waktu yang valid.',
            'end_date.after' => 'Waktu selesai harus setelah waktu mulai.',
            'approver_level_1.different' => 'Approver level 1 dan level 2 harus orang yang berbeda.',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        DB::transaction(function () use ($request, $validated, $startDate, $endDate): void {
            $vehicle = Vehicle::query()->whereKey($validated['vehicle_id'])->lockForUpdate()->firstOrFail();

            if ($vehicle->status === 'service') {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'Kendaraan berstatus service tidak dapat dipesan.',
                ]);
            }

            Driver::query()->whereKey($validated['driver_id'])->lockForUpdate()->firstOrFail();

            $hasConflict = Booking::query()
                ->where('vehicle_id', $validated['vehicle_id'])
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_date', '<', $endDate)
                ->where('end_date', '>', $startDate)
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'Kendaraan sudah memiliki pemesanan pending atau approved pada rentang waktu tersebut.',
                ]);
            }

            $hasDriverConflict = Booking::query()
                ->where('driver_id', $validated['driver_id'])
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_date', '<', $endDate)
                ->where('end_date', '>', $startDate)
                ->exists();

            if ($hasDriverConflict) {
                throw ValidationException::withMessages([
                    'driver_id' => 'Pengemudi sudah memiliki pemesanan pending atau approved pada rentang waktu tersebut.',
                ]);
            }

            $booking = Booking::create([
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'created_by' => $request->user()->id,
                'requester_name' => $validated['requester_name'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'purpose' => $validated['purpose'],
                'status' => 'pending',
            ]);

            $booking->approvals()->createMany([
                [
                    'approver_id' => $validated['approver_level_1'],
                    'level' => 1,
                    'status' => 'pending',
                ],
                [
                    'approver_id' => $validated['approver_level_2'],
                    'level' => 2,
                    'status' => 'pending',
                ],
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'create',
                'entity' => 'booking',
                'entity_id' => $booking->id,
                'description' => "Pemesanan kendaraan {$vehicle->plate_number} dibuat.",
            ]);
        });

        return redirect()->route('bookings.index')->with('status', 'Pemesanan berhasil diajukan.');
    }
}
