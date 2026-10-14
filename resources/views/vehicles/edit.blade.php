<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('vehicles.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Kembali ke kendaraan</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-900">Edit Kendaraan</h1>
        </div>

        <form action="{{ route('vehicles.update', $vehicle) }}" method="POST" class="space-y-6 rounded-lg bg-white p-6 shadow">
            @csrf
            @method('PUT')
            @include('vehicles._form', ['vehicle' => $vehicle])
        </form>
    </div>
</x-app-layout>
