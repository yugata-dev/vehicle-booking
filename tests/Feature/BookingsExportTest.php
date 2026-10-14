<?php

namespace Tests\Feature;

use App\Exports\BookingsExport;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BookingsExportTest extends TestCase
{
    public function test_authenticated_user_can_download_a_date_filtered_booking_export(): void
    {
        $user = User::factory()->make([
            'id' => 123,
            'role' => 'employee',
        ]);

        Excel::fake();

        $response = $this->actingAs($user)->get(route('bookings.export', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertOk();

        Excel::assertDownloaded('bookings.xlsx', function (BookingsExport $export): bool {
            $query = $export->query();

            return $export->headings() === [
                'ID',
                'Plat Kendaraan',
                'Nama Driver',
                'Pemohon',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Tujuan',
                'Status',
            ]
                && in_array(123, $query->getBindings(), true)
                && in_array('2026-10-01', $query->getBindings(), true)
                && in_array('2026-10-31', $query->getBindings(), true);
        });
    }

    public function test_export_rejects_an_end_date_before_the_start_date(): void
    {
        $user = User::factory()->make([
            'id' => 123,
            'role' => 'employee',
        ]);

        Excel::fake();

        $response = $this->actingAs($user)->get(route('bookings.export', [
            'start_date' => '2026-10-31',
            'end_date' => '2026-10-01',
        ]));

        $response->assertRedirect()
            ->assertSessionHasErrors('end_date');
    }
}
