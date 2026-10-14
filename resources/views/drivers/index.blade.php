<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Daftar Pengemudi</h1>
            <a href="{{ route('drivers.create') }}" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                Tambah Pengemudi
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nomor SIM</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Telepon</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($drivers as $driver)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $driver->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $driver->license_number ?: '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $driver->phone }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex justify-end gap-4">
                                        <a href="{{ route('drivers.edit', $driver) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                        <form action="{{ route('drivers.destroy', $driver) }}" method="POST" onsubmit="return confirm('Hapus pengemudi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada pengemudi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $drivers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
