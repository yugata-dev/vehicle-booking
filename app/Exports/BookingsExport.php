<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly ?string $startDate = null,
        private readonly ?string $endDate = null,
    ) {}

    /**
     * @return Builder<Booking>
     */
    public function query(): Builder
    {
        return Booking::query()
            ->with(['vehicle', 'driver'])
            ->when($this->user->role !== 'admin', fn (Builder $query) => $query->where('created_by', $this->user->id))
            ->when($this->startDate, fn (Builder $query, string $startDate) => $query->whereDate('end_date', '>=', $startDate))
            ->when($this->endDate, fn (Builder $query, string $endDate) => $query->whereDate('start_date', '<=', $endDate))
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Plat Kendaraan',
            'Nama Driver',
            'Pemohon',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Tujuan',
            'Status',
        ];
    }

    /**
     * @param  Booking  $booking
     * @return array<int, int|string>
     */
    public function map(mixed $booking): array
    {
        return [
            $booking->id,
            $booking->vehicle->plate_number,
            $booking->driver->name,
            $booking->requester_name,
            $booking->start_date->format('Y-m-d H:i:s'),
            $booking->end_date->format('Y-m-d H:i:s'),
            $booking->purpose,
            $booking->status,
        ];
    }
}
