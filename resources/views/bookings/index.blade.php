<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Daftar Pemesanan</h1>
            <a href="{{ route('bookings.create') }}" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">Ajukan Pemesanan</a>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('bookings.index') }}" class="grid grid-cols-1 gap-4 rounded-lg bg-white p-6 shadow sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="filter_status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="filter_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua status</option>
                    @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_vehicle" class="block text-sm font-medium text-gray-700">Kendaraan</label>
                <select id="filter_vehicle" name="vehicle_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua kendaraan</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(($filters['vehicle_id'] ?? '') == $vehicle->id)>{{ $vehicle->plate_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_from" class="block text-sm font-medium text-gray-700">Tanggal mulai dari</label>
                <input id="filter_from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="filter_to" class="block text-sm font-medium text-gray-700">Tanggal sampai</label>
                <input id="filter_to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex gap-3 sm:col-span-2 lg:col-span-4">
                <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Filter</button>
                <a href="{{ route('bookings.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>

        <form method="GET" action="{{ route('bookings.export') }}" class="flex flex-col items-end gap-4 rounded-lg bg-white p-6 shadow sm:flex-row sm:justify-end">
            <div class="w-full sm:w-auto">
                <label for="export_start_date" class="block text-sm font-medium text-gray-700">Mulai</label>
                <input id="export_start_date" type="date" name="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="w-full sm:w-auto">
                <label for="export_end_date" class="block text-sm font-medium text-gray-700">Sampai</label>
                <input id="export_end_date" type="date" name="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <button type="submit" class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Export Excel</button>
        </form>

        <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Kendaraan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pemohon</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pengemudi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->vehicle->plate_number }} — {{ $booking->vehicle->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->requester_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->driver->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $booking->start_date->format('d/m/Y H:i') }}–{{ $booking->end_date->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($booking->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada pemesanan yang cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 pt-4">{{ $bookings->links() }}</div>
        </div>
    </div>
</x-app-layout>
