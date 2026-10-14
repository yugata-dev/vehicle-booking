<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        return view('vehicles.index', [
            'vehicles' => Vehicle::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:255', Rule::unique('vehicles', 'plate_number')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['orang', 'barang'])],
            'ownership' => ['required', Rule::in(['milik', 'sewa'])],
            'rental_company' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $vehicle = Vehicle::create($validated);

            $request->user()->activityLogs()->create([
                'action' => 'created',
                'entity' => 'vehicle',
                'entity_id' => $vehicle->id,
                'description' => "Kendaraan {$vehicle->name} ({$vehicle->plate_number}) dibuat.",
            ]);
        });

        return redirect()->route('vehicles.index')->with('status', 'Kendaraan berhasil ditambahkan.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', [
            'vehicle' => $vehicle,
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:255', Rule::unique('vehicles', 'plate_number')->ignore($vehicle)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['orang', 'barang'])],
            'ownership' => ['required', Rule::in(['milik', 'sewa'])],
            'rental_company' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $validated, $vehicle): void {
            $vehicle->update($validated);

            $request->user()->activityLogs()->create([
                'action' => 'updated',
                'entity' => 'vehicle',
                'entity_id' => $vehicle->id,
                'description' => "Kendaraan {$vehicle->name} ({$vehicle->plate_number}) diperbarui.",
            ]);
        });

        return redirect()->route('vehicles.index')->with('status', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        DB::transaction(function () use ($request, $vehicle): void {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'deleted',
                'entity' => 'vehicle',
                'entity_id' => $vehicle->id,
                'description' => "Kendaraan {$vehicle->name} ({$vehicle->plate_number}) dihapus.",
            ]);

            $vehicle->delete();
        });

        return redirect()->route('vehicles.index')->with('status', 'Kendaraan berhasil dihapus.');
    }
}
