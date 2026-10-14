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

    public function test_booking_form_lists_resources_and_approvers(): void
    {
        $requester = User::factory()->create(['role' => 'employee']);
        $vehicle = $this->createVehicle();
        $driver = $this->createDriver();
        $approver = User::factory()->create(['role' => 'approver']);
        User::factory()->create(['role' => 'employee', 'name' => 'Not an approver']);

        $response = $this->actingAs($requester)->get(route('bookings.create'));

        $response->assertOk()
            ->assertSee($vehicle->plate_number)
            ->assertSee($driver->name)
            ->assertSee($approver->name)
            ->assertDontSee('Not an approver');
    }

    public function test_valid_request_creates_booking_approvals_and_activity_log(): void
    {
        $requester = User::factory()->create(['role' => 'employee']);
        $vehicle = $this->createVehicle();
        $driver = $this->createDriver();
        $levelOne = User::factory()->create(['role' => 'approver']);
        $levelTwo = User::factory()->create(['role' => 'approver']);

        $response = $this->actingAs($requester)->post(route('bookings.store'), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'requester_name' => 'Requester Name',
            'start_date' => '2026-10-15T09:00',
            'end_date' => '2026-10-15T11:00',
            'purpose' => 'Client visit',
            'approver_level_1' => $levelOne->id,
            'approver_level_2' => $levelTwo->id,
        ]);

        $booking = Booking::query()->sole();

        $response->assertRedirect(route('bookings.index'));
        $this->assertSame('pending', $booking->status);
        $this->assertSame($requester->id, $booking->created_by);
        $this->assertDatabaseHas('approvals', [
            'booking_id' => $booking->id,
            'approver_id' => $levelOne->id,
            'level' => 1,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('approvals', [
            'booking_id' => $booking->id,
            'approver_id' => $levelTwo->id,
            'level' => 2,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $requester->id,
            'action' => 'create',
            'entity' => 'booking',
            'entity_id' => $booking->id,
        ]);
    }

    public function test_overlapping_pending_booking_is_rejected(): void
    {
        $requester = User::factory()->create(['role' => 'employee']);
        $vehicle = $this->createVehicle();
        $driver = $this->createDriver();
        $approver = User::factory()->create(['role' => 'approver']);

        Booking::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'created_by' => $requester->id,
            'requester_name' => 'Existing Requester',
            'start_date' => '2026-10-15 10:00:00',
            'end_date' => '2026-10-15 12:00:00',
            'purpose' => 'Existing booking',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($requester)->from(route('bookings.create'))->post(route('bookings.store'), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'requester_name' => 'Requester Name',
            'start_date' => '2026-10-15T11:00',
            'end_date' => '2026-10-15T13:00',
            'purpose' => 'Overlapping booking',
            'approver_level_1' => $approver->id,
            'approver_level_2' => $approver->id,
        ]);

        $response->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors('vehicle_id');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('approvals', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_booking_cannot_be_assigned_to_a_non_approver(): void
    {
        $requester = User::factory()->create(['role' => 'employee']);
        $vehicle = $this->createVehicle();
        $driver = $this->createDriver();
        $approver = User::factory()->create(['role' => 'approver']);
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($requester)->post(route('bookings.store'), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'requester_name' => 'Requester Name',
            'start_date' => '2026-10-15T09:00',
            'end_date' => '2026-10-15T11:00',
            'purpose' => 'Client visit',
            'approver_level_1' => $approver->id,
            'approver_level_2' => $employee->id,
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('approver_level_2');

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('approvals', 0);
    }

    public function test_booking_index_only_shows_the_requesters_bookings(): void
    {
        $requester = User::factory()->create(['role' => 'employee']);
        $otherRequester = User::factory()->create(['role' => 'employee']);
        $vehicle = $this->createVehicle();
        $driver = $this->createDriver();

        foreach ([$requester, $otherRequester] as $owner) {
            Booking::create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'created_by' => $owner->id,
                'requester_name' => $owner->name,
                'start_date' => '2026-10-16 09:00:00',
                'end_date' => '2026-10-16 10:00:00',
                'purpose' => $owner === $requester ? 'Visible booking purpose' : 'Private booking purpose',
                'status' => 'pending',
            ]);
        }

        $response = $this->actingAs($requester)->get(route('bookings.index'));

        $response->assertOk()
            ->assertSee('Visible booking purpose')
            ->assertDontSee('Private booking purpose');
    }

    private function createVehicle(): Vehicle
    {
        return Vehicle::create([
            'plate_number' => 'ABC-1234',
            'name' => 'Pool Car',
            'type' => 'orang',
            'ownership' => 'milik',
            'status' => 'tersedia',
        ]);
    }

    private function createDriver(): Driver
    {
        return Driver::create([
            'name' => 'Pool Driver',
            'license_number' => 'SIM-1234',
            'phone' => '081234567890',
        ]);
    }
}
