<div>
    <label for="plate_number" class="block text-sm font-medium text-gray-700">Plat nomor</label>
    <input id="plate_number" name="plate_number" value="{{ old('plate_number', $vehicle?->plate_number) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <x-input-error :messages="$errors->get('plate_number')" class="mt-2" />
</div>

<div>
    <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
    <input id="name" name="name" value="{{ old('name', $vehicle?->name) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <label for="type" class="block text-sm font-medium text-gray-700">Tipe</label>
    <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <option value="">Pilih tipe</option>
        <option value="orang" @selected(old('type', $vehicle?->type) === 'orang')>Orang</option>
        <option value="barang" @selected(old('type', $vehicle?->type) === 'barang')>Barang</option>
    </select>
    <x-input-error :messages="$errors->get('type')" class="mt-2" />
</div>

<div>
    <label for="ownership" class="block text-sm font-medium text-gray-700">Kepemilikan</label>
    <select id="ownership" name="ownership" required onchange="document.getElementById('rental-company-field').hidden = this.value !== 'sewa'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <option value="">Pilih kepemilikan</option>
        <option value="milik" @selected(old('ownership', $vehicle?->ownership) === 'milik')>Milik</option>
        <option value="sewa" @selected(old('ownership', $vehicle?->ownership) === 'sewa')>Sewa</option>
    </select>
    <x-input-error :messages="$errors->get('ownership')" class="mt-2" />
</div>

<div id="rental-company-field" @if(old('ownership', $vehicle?->ownership) !== 'sewa') hidden @endif>
    <label for="rental_company" class="block text-sm font-medium text-gray-700">Perusahaan rental (opsional)</label>
    <input id="rental_company" name="rental_company" value="{{ old('rental_company', $vehicle?->rental_company) }}" maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <x-input-error :messages="$errors->get('rental_company')" class="mt-2" />
</div>

<div class="flex justify-end gap-3">
    <a href="{{ route('vehicles.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Simpan</button>
</div>
