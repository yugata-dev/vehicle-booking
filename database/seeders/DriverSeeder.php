<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Seed the application's drivers.
     */
    public function run(): void
    {
        $drivers = [
            ['name' => 'Budi Santoso', 'license_number' => 'SIM-1001', 'phone' => '081234567890'],
            ['name' => 'Ahmad Wijaya', 'license_number' => 'SIM-1002', 'phone' => '081234567891'],
            ['name' => 'Siti Aminah', 'license_number' => 'SIM-1003', 'phone' => '081234567892'],
            ['name' => 'Dedi Kurniawan', 'license_number' => 'SIM-1004', 'phone' => '081234567893'],
            ['name' => 'Rina Marlina', 'license_number' => 'SIM-1005', 'phone' => '081234567894'],
        ];

        foreach ($drivers as $driver) {
            Driver::updateOrCreate(
                ['license_number' => $driver['license_number']],
                $driver,
            );
        }
    }
}
