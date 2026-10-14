<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-medium text-blue-700">Manajemen armada</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Daftar pemesanan</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola, filter, dan ekspor data pemesanan kendaraan.</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <x-heroicon-o-plus class="h-4 w-4" />
                Ajukan pemesanan
            </a>
        </div>

        @if (session('status'))
            <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="GET" action="{{ route('bookings.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.02] sm:p-6">
            <div class="mb-5">
                <h2 class="text-sm font-semibold text-slate-900">Filter pemesanan</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih kriteria untuk mempersempit daftar.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="filter_status" class="block text-sm font-medium text-slate-700">Status</label>
                    <select id="filter_status" name="status" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua status</option>
                        @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_vehicle" class="block text-sm font-medium text-slate-700">Kendaraan</label>
                    <select id="filter_vehicle" name="vehicle_id" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua kendaraan</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(($filters['vehicle_id'] ?? '') == $vehicle->id)>{{ $vehicle->plate_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_from" class="block text-sm font-medium text-slate-700">Tanggal mulai dari</label>
                    <input id="filter_from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="filter_to" class="block text-sm font-medium text-slate-700">Tanggal sampai</label>
                    <input id="filter_to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">Terapkan filter</button>
                <a href="{{ route('bookings.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
            </div>
        </form>

        <form method="GET" action="{{ route('bookings.export') }}" class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.02] sm:flex-row sm:items-end sm:p-6">
            <div class="min-w-0 flex-1">
                <h2 class="text-sm font-semibold text-slate-900">Ekspor data</h2>
                <p class="mt-1 text-sm text-slate-500">Unduh pemesanan berdasarkan rentang tanggal.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label for="export_start_date" class="block text-sm font-medium text-slate-700">Mulai</label>
                    <input id="export_start_date" type="date" name="start_date" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="export_end_date" class="block text-sm font-medium text-slate-700">Sampai</label>
                    <input id="export_end_date" type="date" name="end_date" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                Export Excel
            </button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/[0.02]">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Semua pemesanan</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $bookings->total() }} data ditemukan</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Kendaraan</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Pemohon</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Pengemudi</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Waktu</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900 sm:px-6">
                                    {{ $booking->vehicle->plate_number }}
                                    <span class="font-normal text-slate-500">— {{ $booking->vehicle->name }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700 sm:px-6">{{ $booking->requester_name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 sm:px-6">{{ $booking->driver->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 sm:px-6">
                                    <span class="block">{{ $booking->start_date->format('d/m/Y H:i') }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-400">sampai {{ $booking->end_date->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                    @php($statusStyle = ['approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20'][$booking->status] ?? 'bg-slate-100 text-slate-700 ring-slate-600/10')
                                    <span @class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset', $statusStyle])>{{ $booking->status === 'approved' ? 'Disetujui' : ($booking->status === 'pending' ? 'Menunggu' : ($booking->status === 'rejected' ? 'Ditolak' : $booking->status)) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <x-heroicon-o-calendar-days class="mx-auto h-9 w-9 text-slate-300" />
                                    <p class="mt-3 text-sm font-medium text-slate-700">Belum ada pemesanan</p>
                                    <p class="mt-1 text-sm text-slate-500">Coba ubah filter atau ajukan pemesanan baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($bookings->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $bookings->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
