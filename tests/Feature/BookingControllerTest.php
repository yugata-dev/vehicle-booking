<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_vehicle_can_be_booked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vehicle = $this->createVehicle('tersedia');
        $driver = $this->createDriver();
        $approvers = [
            User::factory()->create(['role' => 'approver']),
            User::factory()->create(['role' => 'approver']),
        ];

        $this->actingAs($admin)
            ->post(route('bookings.store'), $this->bookingPayload($vehicle, $driver, $approvers))
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('status', 'Pemesanan berhasil diajukan.');

        $this->assertDatabaseHas('bookings', [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('approvals', 2);
    }

    public function test_service_vehicle_is_not_offered_or_bookable_by_direct_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vehicle = $this->createVehicle('service');
        $driver = $this->createDriver();
        $approvers = [
            User::factory()->create(['role' => 'approver']),
            User::factory()->create(['role' => 'approver']),
        ];

        $this->actingAs($admin)
            ->get(route('bookings.create'))
            ->assertOk()
            ->assertDontSee($vehicle->plate_number)
            ->assertSee('Kendaraan yang sedang diservis tidak ditampilkan.')
            ->assertSee('Setelah servis selesai, ubah statusnya menjadi tersedia melalui menu Kendaraan.');

        $this->post(route('bookings.store'), $this->bookingPayload($vehicle, $driver, $approvers))
            ->assertSessionHasErrors('vehicle_id');

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('approvals', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_overlapping_booking_for_same_driver_is_rejected_across_different_vehicles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = $this->createDriver();
        $firstVehicle = $this->createVehicle('tersedia');
        $secondVehicle = $this->createVehicle('tersedia');
        $approvers = $this->createApprovers();
        $this->createExistingBooking($admin, $firstVehicle, $driver, 'pending', '09:00', '11:00');

        $this->actingAs($admin)
            ->post(route('bookings.store'), $this->bookingPayload($secondVehicle, $driver, $approvers, '10:00', '12:00'))
            ->assertSessionHasErrors('driver_id');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('approvals', 0);
    }

    public function test_overlapping_booking_for_same_vehicle_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vehicle = $this->createVehicle('tersedia');
        $this->createExistingBooking($admin, $vehicle, $this->createDriver(), 'approved', '09:00', '11:00');
        $driver = $this->createDriver();

        $this->actingAs($admin)
            ->post(route('bookings.store'), $this->bookingPayload($vehicle, $driver, $this->createApprovers(), '10:00', '12:00'))
            ->assertSessionHasErrors('vehicle_id');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('approvals', 0);
    }

    public function test_adjacent_booking_periods_are_allowed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vehicle = $this->createVehicle('tersedia');
        $driver = $this->createDriver();
        $this->createExistingBooking($admin, $vehicle, $driver, 'pending', '09:00', '10:00');

        $this->actingAs($admin)
            ->post(route('bookings.store'), $this->bookingPayload($vehicle, $driver, $this->createApprovers(), '10:00', '11:00'))
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseCount('approvals', 2);
    }

    public function test_rejected_booking_does_not_block_vehicle_or_driver_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vehicle = $this->createVehicle('tersedia');
        $driver = $this->createDriver();
        $this->createExistingBooking($admin, $vehicle, $driver, 'rejected', '09:00', '11:00');

        $this->actingAs($admin)
            ->post(route('bookings.store'), $this->bookingPayload($vehicle, $driver, $this->createApprovers(), '10:00', '12:00'))
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseCount('approvals', 2);
    }

    private int $sequence = 0;

    private function createVehicle(string $status): Vehicle
    {
        $sequence = ++$this->sequence;

        return Vehicle::create([
            'plate_number' => strtoupper($status).'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'name' => 'Pool Car',
            'type' => 'orang',
            'ownership' => 'milik',
            'status' => $status,
        ]);
    }

    private function createDriver(): Driver
    {
        $sequence = ++$this->sequence;

        return Driver::create([
            'name' => 'Pool Driver '.$sequence,
            'license_number' => 'SIM-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'phone' => '081234567890',
        ]);
    }

    private function bookingPayload(Vehicle $vehicle, Driver $driver, array $approvers, string $start = '09:00', string $end = '10:00'): array
    {
        return [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'requester_name' => 'Admin Requester',
            'start_date' => '2026-10-15T'.$start,
            'end_date' => '2026-10-15T'.$end,
            'purpose' => 'Regression test booking',
            'approver_level_1' => $approvers[0]->id,
            'approver_level_2' => $approvers[1]->id,
        ];
    }

    private function createApprovers(): array
    {
        return [
            User::factory()->create(['role' => 'approver']),
            User::factory()->create(['role' => 'approver']),
        ];
    }

    private function createExistingBooking(User $admin, Vehicle $vehicle, Driver $driver, string $status, string $start, string $end): Booking
    {
        return Booking::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'created_by' => $admin->id,
            'requester_name' => 'Existing Requester',
            'start_date' => '2026-10-15 '.$start,
            'end_date' => '2026-10-15 '.$end,
            'purpose' => 'Existing test booking',
            'status' => $status,
        ]);
    }
}
