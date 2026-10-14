<x-app-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Ringkasan pemakaian kendaraan berdasarkan bulan pemesanan.</p>
        </div>

        <section class="rounded-lg bg-white p-6 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Pemakaian Kendaraan per Bulan</h2>
            <div class="relative mt-6 h-80">
                <canvas id="vehicle-usage-chart" aria-label="Grafik pemakaian kendaraan per bulan" role="img"></canvas>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('vehicle-usage-chart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyUsage['labels']),
                datasets: [{
                    label: 'Jumlah pemakaian',
                    data: @json($monthlyUsage['values']),
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: 'rgb(37, 99, 235)',
                    borderWidth: 1,
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                    },
                },
            },
        });
    </script>
</x-app-layout>
