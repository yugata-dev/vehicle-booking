<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Approval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $approvals = Approval::query()
            ->with(['booking.vehicle', 'booking.driver', 'booking.creator'])
            ->where('approver_id', $request->user()->id)
            ->where('status', 'pending')
            ->whereHas('booking', fn($query) => $query
                ->where('status', 'pending'))
            ->where(function ($query) {
                $query->where('level', 1)
                    ->orWhereHas('booking.approvals', fn($levelOne) => $levelOne
                        ->where('level', 1)
                        ->where('status', 'approved'));
            })
            ->latest()
            ->paginate(15);

        return view('approvals.index', [
            'approvals' => $approvals,
        ]);
    }

    public function approve(
        Request $request,
        Approval $approval
    ): RedirectResponse {
        abort_unless(
            (int) $approval->approver_id === (int) $request->user()->id,
            403
        );

        $result = DB::transaction(function () use ($request, $approval): string {
            $approval = Approval::query()
                ->lockForUpdate()
                ->findOrFail($approval->id);

            abort_unless(
                (int) $approval->approver_id === (int) $request->user()->id,
                403
            );

            $booking = $approval->booking()
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $approval->status !== 'pending'
                || $booking->status !== 'pending'
            ) {
                return 'already-decided';
            }

            if (
                (int) $approval->level === 2
                && ! $booking->approvals()
                    ->where('level', 1)
                    ->where('status', 'approved')
                    ->exists()
            ) {
                return 'level-one-required';
            }

            $approval->update([
                'status' => 'approved',
                'decided_at' => now(),
            ]);

            if ((int) $approval->level === 2) {
                $booking->update([
                    'status' => 'approved',
                ]);
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'approve',
                'entity' => 'booking',
                'entity_id' => $booking->id,
                'description' => "Pemesanan disetujui pada level {$approval->level}.",
            ]);

            return 'approved';
        });

        if ($result === 'level-one-required') {
            return back()->with(
                'error',
                'Persetujuan level 1 harus diselesaikan terlebih dahulu.'
            );
        }

        if ($result === 'already-decided') {
            return back()->with(
                'error',
                'Persetujuan ini sudah diproses.'
            );
        }

        return back()->with(
            'status',
            'Pemesanan berhasil disetujui.'
        );
    }

    public function reject(
        Request $request,
        Approval $approval
    ): RedirectResponse {
        abort_unless(
            (int) $approval->approver_id === (int) $request->user()->id,
            403
        );

        $result = DB::transaction(function () use ($request, $approval): string {
            $approval = Approval::query()
                ->lockForUpdate()
                ->findOrFail($approval->id);

            abort_unless(
                (int) $approval->approver_id === (int) $request->user()->id,
                403
            );

            $booking = $approval->booking()
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $approval->status !== 'pending'
                || $booking->status !== 'pending'
            ) {
                return 'already-decided';
            }

            if (
                (int) $approval->level === 2
                && ! $booking->approvals()
                    ->where('level', 1)
                    ->where('status', 'approved')
                    ->exists()
            ) {
                return 'level-one-required';
            }

            $approval->update([
                'status' => 'rejected',
                'decided_at' => now(),
            ]);

            $booking->update([
                'status' => 'rejected',
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'reject',
                'entity' => 'booking',
                'entity_id' => $booking->id,
                'description' => "Pemesanan ditolak pada level {$approval->level}.",
            ]);

            return 'rejected';
        });

        if ($result === 'level-one-required') {
            return back()->with(
                'error',
                'Persetujuan level 1 harus diselesaikan terlebih dahulu.'
            );
        }

        if ($result === 'already-decided') {
            return back()->with(
                'error',
                'Persetujuan ini sudah diproses.'
            );
        }

        return back()->with(
            'status',
            'Pemesanan berhasil ditolak.'
        );
    }
}
