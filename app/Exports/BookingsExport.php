<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingsExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
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
            ->when($this->startDate, fn (Builder $query, string $startDate) => $query->whereDate('start_date', '>=', $startDate))
            ->when($this->endDate, fn (Builder $query, string $endDate) => $query->whereDate('start_date', '<=', $endDate))
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Plat Nomor',
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
            $booking->vehicle?->plate_number ?? '',
            $booking->driver?->name ?? '',
            $booking->requester_name,
            $booking->start_date ? Carbon::parse($booking->start_date)->locale('id')->translatedFormat('d M Y H:i') : '',
            $booking->end_date ? Carbon::parse($booking->end_date)->locale('id')->translatedFormat('d M Y H:i') : '',
            $booking->purpose,
            $booking->status,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 16,
            'C' => 22,
            'D' => 22,
            'E' => 18,
            'F' => 18,
            'G' => 45,
            'H' => 14,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        $styles = [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ],
        ];

        if ($lastRow >= 2) {
            $styles["A2:H{$lastRow}"] = [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ];
            $styles["E2:F{$lastRow}"] = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $styles["H2:H{$lastRow}"] = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
        }

        return $styles;
    }

    /**
     * @return array<string, callable(AfterSheet): void>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $lastRow; $row++) {
                    $statusCell = $sheet->getCell("H{$row}");
                    $status = strtolower((string) $statusCell->getValue());

                    $colors = match ($status) {
                        'approved' => ['D1FAE5', '065F46'],
                        'pending' => ['FEF3C7', '92400E'],
                        'rejected' => ['FEE2E2', '991B1B'],
                        default => null,
                    };

                    if ($colors !== null) {
                        $statusCell->getStyle()->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $colors[0]],
                            ],
                            'font' => [
                                'color' => ['rgb' => $colors[1]],
                            ],
                        ]);
                    }
                }

                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }
}
