<?php

namespace Tests\Feature;

use App\Exports\BookingsExport;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_download_a_date_filtered_booking_export(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Excel::fake();

        $response = $this->actingAs($user)->get(route('bookings.export', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertOk();

        Excel::assertDownloaded('bookings.xlsx', function (BookingsExport $export) use ($user): bool {
            $query = $export->query();

            return $export->headings() === [
                'ID',
                'Plat Nomor',
                'Nama Driver',
                'Pemohon',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Tujuan',
                'Status',
            ]
                && in_array('2026-10-01', $query->getBindings(), true)
                && in_array('2026-10-31', $query->getBindings(), true);
        });

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'export',
            'entity' => 'booking_export',
            'entity_id' => null,
            'description' => 'Ekspor pemesanan untuk rentang tanggal 2026-10-01 sampai 2026-10-31.',
        ]);
    }

    public function test_export_rejects_an_end_date_before_the_start_date(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Excel::fake();

        $response = $this->actingAs($user)->get(route('bookings.export', [
            'start_date' => '2026-10-31',
            'end_date' => '2026-10-01',
        ]));

        $response->assertRedirect()
            ->assertSessionHasErrors('end_date');
    }

    public function test_export_filters_one_day_and_includes_both_date_boundaries(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->createBooking($user, '2026-10-10 00:00:00');
        $this->createBooking($user, '2026-10-10 23:59:59');
        $this->createBooking($user, '2026-10-11 00:00:00');

        Excel::fake();
        $response = $this->actingAs($user)->get(route('bookings.export', [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-10',
        ]));

        $response->assertOk();
        Excel::assertDownloaded('bookings.xlsx', function (BookingsExport $export): bool {
            $query = $export->query();
            $exportedIds = $query->pluck('id')->all();
            $manualCount = Booking::query()
                ->whereDate('start_date', '>=', '2026-10-10')
                ->whereDate('start_date', '<=', '2026-10-10')
                ->count();

            return count($exportedIds) === $manualCount && count($exportedIds) === 2;
        });
    }

    public function test_export_filters_a_multi_day_range_and_matches_manual_count(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->createBooking($user, '2026-10-09 23:59:59');
        $this->createBooking($user, '2026-10-10 12:00:00');
        $this->createBooking($user, '2026-10-12 12:00:00');
        $this->createBooking($user, '2026-10-13 00:00:00');

        Excel::fake();
        $this->actingAs($user)->get(route('bookings.export', [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12',
        ]))->assertOk();

        Excel::assertDownloaded('bookings.xlsx', function (BookingsExport $export): bool {
            $query = $export->query();
            $exportedIds = $query->pluck('id')->all();
            $manualCount = Booking::query()
                ->whereBetween(DB::raw('DATE(start_date)'), ['2026-10-10', '2026-10-12'])
                ->count();

            return count($exportedIds) === $manualCount && count($exportedIds) === 2;
        });
    }

    public function test_export_with_no_matching_bookings_has_zero_rows(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->createBooking($user, '2026-10-01 12:00:00');

        Excel::fake();
        $this->actingAs($user)->get(route('bookings.export', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]))->assertOk();

        Excel::assertDownloaded('bookings.xlsx', fn (BookingsExport $export): bool => $export->query()->count() === Booking::query()
            ->whereBetween(DB::raw('DATE(start_date)'), ['2026-11-01', '2026-11-30'])
            ->count() && $export->query()->count() === 0);
    }

    public function test_export_without_filters_includes_all_bookings(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->createBooking($user, '2026-10-01 12:00:00');
        $this->createBooking($user, '2026-11-01 12:00:00');

        Excel::fake();
        $this->actingAs($user)->get(route('bookings.export'))->assertOk();

        Excel::assertDownloaded('bookings.xlsx', fn (BookingsExport $export): bool => $export->query()->count() === Booking::count());
    }

    private function createBooking(User $user, string $startDate): Booking
    {
        $vehicle = Vehicle::create([
            'plate_number' => fake()->unique()->bothify('B-####-???'),
            'name' => 'Test vehicle',
            'type' => 'orang',
            'ownership' => 'milik',
            'status' => 'tersedia',
        ]);
        $driver = Driver::create([
            'name' => 'Test driver',
            'phone' => '081234567890',
        ]);

        return Booking::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'requester_name' => 'Test requester',
            'start_date' => $startDate,
            'end_date' => $startDate,
            'purpose' => 'Export test',
            'status' => 'pending',
        ]);
    }
}
