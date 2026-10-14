<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan Pemesanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('status') || session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('status') ?? session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if ($approvals->isEmpty())
                        <p class="text-gray-500 text-center py-8">
                            Tidak ada pemesanan yang menunggu persetujuan Anda.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-[900px] divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kendaraan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tujuan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($approvals as $approval)
                                        <tr>
                                            <td class="px-4 py-3">
                                                {{ $approval->booking->vehicle->plate_number }} - {{ $approval->booking->vehicle->name }}
                                            </td>
                                            <td class="px-4 py-3">{{ $approval->booking->driver->name }}</td>
                                            <td class="px-4 py-3">{{ $approval->booking->requester_name }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ \Carbon\Carbon::parse($approval->booking->start_date)->format('d M Y H:i') }}<br>
                                                s/d {{ \Carbon\Carbon::parse($approval->booking->end_date)->format('d M Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ $approval->booking->purpose }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                                    Level {{ $approval->level }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <form method="POST" action="{{ route('approvals.approve', $approval) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                        Setujui
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('approvals.reject', $approval) }}" class="inline ml-2">
                                                    @csrf
                                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                                        Tolak
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
