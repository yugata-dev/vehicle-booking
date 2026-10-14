<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Seed the application's vehicle fleet.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'plate_number' => 'B 1234 XYZ',
                'name' => 'Toyota Hilux',
                'type' => 'orang',
                'ownership' => 'milik',
                'rental_company' => null,
            ],
            [
                'plate_number' => 'B 2345 XYZ',
                'name' => 'Toyota Innova',
                'type' => 'orang',
                'ownership' => 'sewa',
                'rental_company' => 'PT Armada Nusantara',
            ],
            [
                'plate_number' => 'B 3456 XYZ',
                'name' => 'Isuzu Panther',
                'type' => 'orang',
                'ownership' => 'milik',
                'rental_company' => null,
            ],
            [
                'plate_number' => 'B 4567 XYZ',
                'name' => 'Mitsubishi Canter',
                'type' => 'barang',
                'ownership' => 'milik',
                'rental_company' => null,
            ],
            [
                'plate_number' => 'B 5678 XYZ',
                'name' => 'Hino Dutro',
                'type' => 'barang',
                'ownership' => 'sewa',
                'rental_company' => 'PT Mitra Kendaraan Tambang',
            ],
            [
                'plate_number' => 'B 6789 XYZ',
                'name' => 'Isuzu Elf',
                'type' => 'barang',
                'ownership' => 'milik',
                'rental_company' => null,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['plate_number' => $vehicle['plate_number']],
                [...$vehicle, 'status' => 'tersedia'],
            );
        }
    }
}
