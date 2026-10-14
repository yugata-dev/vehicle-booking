<div>
    <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
    <input id="name" name="name" value="{{ old('name', $driver?->name) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <label for="license_number" class="block text-sm font-medium text-gray-700">Nomor SIM (opsional)</label>
    <input id="license_number" name="license_number" value="{{ old('license_number', $driver?->license_number) }}" maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <x-input-error :messages="$errors->get('license_number')" class="mt-2" />
</div>

<div>
    <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
    <input id="phone" name="phone" value="{{ old('phone', $driver?->phone) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div class="flex justify-end gap-3">
    <a href="{{ route('drivers.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Simpan</button>
</div>
