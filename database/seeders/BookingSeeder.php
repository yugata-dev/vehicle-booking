<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RuntimeException;

class BookingSeeder extends Seeder
{
    /**
     * Seed dummy bookings and their approval records.
     */
    public function run(): void
    {
        $approvers = User::where('role', 'approver')->get()->take(2)->values();
        $admin = User::where('role', 'admin')->first();

        if ($approvers->count() < 2) {
            throw new RuntimeException('BookingSeeder requires two users with the approver role.');
        }

        if (! $admin) {
            throw new RuntimeException('BookingSeeder requires a user with the admin role.');
        }

        $requesterNames = [
            'Budi Santoso',
            'Siti Aminah',
            'Andi Wijaya',
            'Rina Marlina',
            'Dedi Kurniawan',
            'Maya Putri',
            'Agus Setiawan',
            'Dewi Lestari',
        ];

        $purposes = [
            'Kunjungan ke tambang site A',
            'Pengiriman spare part',
            'Inspeksi area operasional',
            'Pengantaran karyawan ke site',
            'Pengambilan sampel nikel',
            'Pengiriman dokumen ke kantor cabang',
        ];

        $earliestStart = now()->subMonths(3);
        $latestStart = now();

        foreach (range(1, 8) as $index) {
            $vehicle = Vehicle::inRandomOrder()->first();
            $driver = Driver::inRandomOrder()->first();

            if (! $vehicle || ! $driver) {
                throw new RuntimeException('BookingSeeder requires at least one vehicle and one driver.');
            }

            $startDate = Carbon::createFromTimestamp(random_int(
                $earliestStart->timestamp,
                $latestStart->timestamp,
            ));
            $endDate = $startDate->copy()->addDays(random_int(1, 5));
            $status = $index <= 6 ? 'approved' : 'pending';

            $booking = Booking::create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'created_by' => $admin->id,
                'requester_name' => fake()->randomElement($requesterNames),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'purpose' => fake()->randomElement($purposes),
                'status' => $status,
            ]);

            foreach ([1, 2] as $level) {
                $booking->approvals()->create([
                    'approver_id' => $approvers->get($level - 1)->id,
                    'level' => $level,
                    'status' => $status,
                    'decided_at' => $status === 'approved' ? now() : null,
                ]);
            }
        }
    }
}
