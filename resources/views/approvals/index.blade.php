<x-app-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Menunggu Persetujuan</h1>
            <p class="mt-1 text-sm text-gray-600">Pemesanan level 2 akan muncul setelah persetujuan level 1 disetujui.</p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
        @endif

        <div class="space-y-4">
            @forelse ($approvals as $approval)
                <article class="space-y-4 rounded-lg bg-white p-6 shadow">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row">
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ $approval->booking->vehicle->plate_number }} — {{ $approval->booking->vehicle->name }}
                            </h2>
                            <p class="text-sm text-gray-600">Persetujuan level {{ $approval->level }} · Pemohon: {{ $approval->booking->requester_name }}</p>
                            <p class="text-sm text-gray-600">Pengemudi: {{ $approval->booking->driver->name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $approval->booking->start_date->format('d/m/Y H:i') }}–{{ $approval->booking->end_date->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-start gap-3">
                            <form method="POST" action="{{ route('approvals.approve', $approval) }}">
                                @csrf
                                <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('approvals.reject', $approval) }}" onsubmit="return confirm('Tolak pemesanan ini?')">
                                @csrf
                                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Tolak</button>
                            </form>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-medium text-gray-900">Tujuan</h3>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $approval->booking->purpose }}</p>
                    </div>
                </article>
            @empty
                <div class="rounded-lg bg-white p-6 text-center text-sm text-gray-500 shadow">Tidak ada persetujuan yang menunggu.</div>
            @endforelse
        </div>

        {{ $approvals->links() }}
    </div>
</x-app-layout>
