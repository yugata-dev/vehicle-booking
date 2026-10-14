<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_two_approval_is_hidden_and_cannot_be_processed_before_level_one(): void
    {
        [$booking, $levelOne, $levelTwo, $levelOneApproval, $levelTwoApproval] = $this->createApprovalWorkflow();

        $this->actingAs($levelTwo)
            ->get(route('approvals.index'))
            ->assertOk()
            ->assertDontSee($booking->purpose);

        $this->post(route('approvals.approve', $levelTwoApproval))
            ->assertRedirect()
            ->assertSessionHas('error', 'Persetujuan level 1 harus diselesaikan terlebih dahulu.');

        $this->assertDatabaseHas('approvals', [
            'id' => $levelTwoApproval->id,
            'status' => 'pending',
        ]);

        $this->actingAs($levelOne)->post(route('approvals.approve', $levelOneApproval))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pemesanan berhasil disetujui.');

        $this->actingAs($levelTwo)
            ->get(route('approvals.index'))
            ->assertOk()
            ->assertSee($booking->purpose);

        $this->post(route('approvals.approve', $levelTwoApproval))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pemesanan berhasil disetujui.');

        $this->assertDatabaseHas('approvals', [
            'id' => $levelOneApproval->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('approvals', [
            'id' => $levelTwoApproval->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $levelTwo->id,
            'action' => 'approve',
            'entity' => 'booking',
            'entity_id' => $booking->id,
        ]);
    }

    public function test_approver_cannot_process_another_users_approval(): void
    {
        [, , , , $approval] = $this->createApprovalWorkflow();
        $unauthorizedApprover = User::factory()->create(['role' => 'approver']);

        $this->actingAs($unauthorizedApprover)
            ->post(route('approvals.approve', $approval))
            ->assertForbidden();
    }

    public function test_rejection_updates_approval_and_booking(): void
    {
        [$booking, $levelOne, , $approval] = $this->createApprovalWorkflow();

        $this->actingAs($levelOne)
            ->post(route('approvals.reject', $approval))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pemesanan berhasil ditolak.');

        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $levelOne->id,
            'action' => 'reject',
            'entity' => 'booking',
            'entity_id' => $booking->id,
        ]);
    }

    public function test_level_two_cannot_approve_booking_after_level_one_rejects_it(): void
    {
        [$booking, $levelOne, $levelTwo, $levelOneApproval, $levelTwoApproval] = $this->createApprovalWorkflow();

        $this->actingAs($levelOne)
            ->post(route('approvals.reject', $levelOneApproval))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pemesanan berhasil ditolak.');

        $this->assertDatabaseHas('approvals', [
            'id' => $levelOneApproval->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($levelTwo)
            ->post(route('approvals.approve', $levelTwoApproval))
            ->assertRedirect()
            ->assertSessionHas('error', 'Persetujuan ini sudah diproses.');

        $this->assertDatabaseHas('approvals', [
            'id' => $levelOneApproval->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('approvals', [
            'id' => $levelTwoApproval->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertDatabaseMissing('activity_logs', [
            'user_id' => $levelTwo->id,
            'action' => 'approve',
            'entity' => 'booking',
            'entity_id' => $booking->id,
        ]);
    }

    public function test_level_two_rejection_updates_booking_and_logs_decision(): void
    {
        [$booking, $levelOne, $levelTwo, $levelOneApproval, $levelTwoApproval] = $this->createApprovalWorkflow();

        $this->actingAs($levelOne)
            ->post(route('approvals.approve', $levelOneApproval))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pemesanan berhasil disetujui.');

        $this->actingAs($levelTwo)
            ->post(route('approvals.reject', $levelTwoApproval))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pemesanan berhasil ditolak.');

        $this->assertDatabaseHas('approvals', [
            'id' => $levelOneApproval->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('approvals', [
            'id' => $levelTwoApproval->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $levelTwo->id,
            'action' => 'reject',
            'entity' => 'booking',
            'entity_id' => $booking->id,
            'description' => 'Pemesanan ditolak pada level 2.',
        ]);
    }

    private function createApprovalWorkflow(): array
    {
        $creator = User::factory()->create(['role' => 'employee']);
        $levelOne = User::factory()->create(['role' => 'approver']);
        $levelTwo = User::factory()->create(['role' => 'approver']);
        $vehicle = Vehicle::create([
            'plate_number' => 'ABC-1234',
            'name' => 'Pool Car',
            'type' => 'orang',
            'ownership' => 'milik',
            'status' => 'tersedia',
        ]);
        $driver = Driver::create([
            'name' => 'Pool Driver',
            'license_number' => 'SIM-1234',
            'phone' => '081234567890',
        ]);
        $booking = Booking::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
            'requester_name' => 'Requester Name',
            'start_date' => '2026-10-15 09:00:00',
            'end_date' => '2026-10-15 11:00:00',
            'purpose' => 'Approval test purpose',
            'status' => 'pending',
        ]);
        $levelOneApproval = Approval::create([
            'booking_id' => $booking->id,
            'approver_id' => $levelOne->id,
            'level' => 1,
            'status' => 'pending',
        ]);
        $levelTwoApproval = Approval::create([
            'booking_id' => $booking->id,
            'approver_id' => $levelTwo->id,
            'level' => 2,
            'status' => 'pending',
        ]);

        return [$booking, $levelOne, $levelTwo, $levelOneApproval, $levelTwoApproval];
    }
}
