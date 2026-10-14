<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(): View
    {
        return view('drivers.index', [
            'drivers' => Driver::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('drivers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255', Rule::unique('drivers', 'license_number')],
            'phone' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $driver = Driver::create($validated);

            $request->user()->activityLogs()->create([
                'action' => 'created',
                'entity' => 'driver',
                'entity_id' => $driver->id,
                'description' => "Pengemudi {$driver->name} dibuat.",
            ]);
        });

        return redirect()->route('drivers.index')->with('status', 'Pengemudi berhasil ditambahkan.');
    }

    public function edit(Driver $driver): View
    {
        return view('drivers.edit', [
            'driver' => $driver,
        ]);
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255', Rule::unique('drivers', 'license_number')->ignore($driver)],
            'phone' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $validated, $driver): void {
            $driver->update($validated);

            $request->user()->activityLogs()->create([
                'action' => 'updated',
                'entity' => 'driver',
                'entity_id' => $driver->id,
                'description' => "Pengemudi {$driver->name} diperbarui.",
            ]);
        });

        return redirect()->route('drivers.index')->with('status', 'Pengemudi berhasil diperbarui.');
    }

    public function destroy(Request $request, Driver $driver): RedirectResponse
    {
        DB::transaction(function () use ($request, $driver): void {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'deleted',
                'entity' => 'driver',
                'entity_id' => $driver->id,
                'description' => "Pengemudi {$driver->name} dihapus.",
            ]);

            $driver->delete();
        });

        return redirect()->route('drivers.index')->with('status', 'Pengemudi berhasil dihapus.');
    }
}
