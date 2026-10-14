<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('bookings.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Kembali ke pemesanan</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-900">Ajukan Pemesanan Kendaraan</h1>
        </div>

        <form action="{{ route('bookings.store') }}" method="POST" class="space-y-6 rounded-lg bg-white p-6 shadow">
            @csrf

            @if ($hasServiceVehicles)
                <aside role="note" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p class="font-semibold">Kendaraan yang sedang diservis tidak ditampilkan.</p>
                    <p class="mt-1">Kendaraan berstatus service belum bisa dipesan. Setelah servis selesai, ubah statusnya menjadi tersedia melalui menu Kendaraan.</p>
                </aside>
            @endif

            <div>
                <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Kendaraan</label>
                <select id="vehicle_id" name="vehicle_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih kendaraan</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                            {{ $vehicle->plate_number }} — {{ $vehicle->name }} ({{ ucfirst($vehicle->status) }})
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
            </div>

            <div>
                <label for="driver_id" class="block text-sm font-medium text-gray-700">Pengemudi</label>
                <select id="driver_id" name="driver_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih pengemudi</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>{{ $driver->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('driver_id')" class="mt-2" />
            </div>

            <div>
                <label for="requester_name" class="block text-sm font-medium text-gray-700">Nama pemohon</label>
                <input id="requester_name" name="requester_name" value="{{ old('requester_name', auth()->user()->name) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <x-input-error :messages="$errors->get('requester_name')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Mulai</label>
                    <input id="start_date" name="start_date" type="datetime-local" value="{{ old('start_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Selesai</label>
                    <input id="end_date" name="end_date" type="datetime-local" value="{{ old('end_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                </div>
            </div>

            <div>
                <label for="purpose" class="block text-sm font-medium text-gray-700">Tujuan pemakaian</label>
                <textarea id="purpose" name="purpose" rows="4" required maxlength="5000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('purpose') }}</textarea>
                <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="approver_level_1" class="block text-sm font-medium text-gray-700">Approver level 1</label>
                    <select id="approver_level_1" name="approver_level_1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih approver level 1</option>
                        @foreach ($approvers as $approver)
                            <option value="{{ $approver->id }}" @selected(old('approver_level_1') == $approver->id)>{{ $approver->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('approver_level_1')" class="mt-2" />
                </div>
                <div>
                    <label for="approver_level_2" class="block text-sm font-medium text-gray-700">Approver level 2</label>
                    <select id="approver_level_2" name="approver_level_2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih approver level 2</option>
                        @foreach ($approvers as $approver)
                            <option value="{{ $approver->id }}" @selected(old('approver_level_2') == $approver->id)>{{ $approver->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('approver_level_2')" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('bookings.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Ajukan pemesanan</button>
            </div>
        </form>
    </div>
</x-app-layout>
