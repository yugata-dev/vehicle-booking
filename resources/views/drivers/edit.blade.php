<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('drivers.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Kembali ke pengemudi</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-900">Edit Pengemudi</h1>
        </div>

        <form action="{{ route('drivers.update', $driver) }}" method="POST" class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            @include('drivers._form', ['driver' => $driver])
        </form>
    </div>
</x-app-layout>
