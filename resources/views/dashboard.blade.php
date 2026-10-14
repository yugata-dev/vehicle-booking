<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Selamat datang, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Ringkasan armada dan pemakaian kendaraan.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.02]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total kendaraan</p>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($totalVehicles) }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <x-heroicon-o-truck class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-4 text-xs text-slate-500">Kendaraan terdaftar dalam armada</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.02]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pemesanan disetujui</p>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($approvedBookings) }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <x-heroicon-o-check-circle class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-4 text-xs text-slate-500">Seluruh pemesanan yang telah disetujui</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/[0.02] sm:col-span-2 xl:col-span-1">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Menunggu persetujuan</p>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($pendingBookings) }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <x-heroicon-o-clock class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-4 text-xs text-slate-500">Pemesanan yang masih perlu diproses</p>
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/[0.02]">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                <h2 class="text-base font-semibold text-slate-900">Pemakaian kendaraan</h2>
                <p class="mt-1 text-sm text-slate-500">Jumlah pemesanan disetujui dalam enam bulan terakhir.</p>
            </div>
            <div class="p-4 sm:p-6">
                <div class="relative h-72 sm:h-80">
                    <canvas id="bookingChart" aria-label="Grafik pemakaian kendaraan selama enam bulan terakhir" role="img"></canvas>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('bookingChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Angkutan Orang',
                        data: @json($chartOrang),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Angkutan Barang',
                        data: @json($chartBarang),
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            borderDash: [3, 3],
                            drawBorder: false,
                        },
                        ticks: { stepSize: 1, color: '#9CA3AF' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9CA3AF' }
                    }
                }
            }
        });
    </script>
</x-app-layout>
