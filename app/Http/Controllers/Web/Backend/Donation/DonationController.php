<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DrawParticipant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DrawParticipant::with(['user:id,name,email,phone,donor_id', 'weeklyDraw:id,week_number', 'donation'])
                ->select('draw_participants.*')
                ->join('weekly_draws', 'draw_participants.weekly_draw_id', '=', 'weekly_draws.id')
                ->join('donations', 'draw_participants.donation_id', '=', 'donations.id')
                ->orderBy('weekly_draws.week_number', 'desc')
                ->orderBy('donations.donated_at', 'desc');

            // Filters
            if ($request->filled('week_id')) {
                $query->where('draw_participants.weekly_draw_id', $request->week_id);
            }

            if ($request->filled('participant_type')) {
                $isRollover = $request->participant_type === 'rollover';
                $query->where('draw_participants.is_rollover', $isRollover);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('donations.donated_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('donations.donated_at', '<=', $request->date_to);
            }

            if ($request->filled('min_amount')) {
                $query->where('donations.amount', '>=', $request->min_amount);
            }

            if ($request->filled('max_amount')) {
                $query->where('donations.amount', '<=', $request->max_amount);
            }

            if ($request->filled('payment_status')) {
                $query->where('donations.stripe_payment_status', $request->payment_status);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('donor_name', fn($row) => $row->user->name ?? 'N/A')
                ->addColumn('donor_id', fn($row) => $row->user->donor_id ?? 'N/A')
                ->addColumn('email', fn($row) => $row->user->email ?? 'N/A')
                ->addColumn('phone', fn($row) => $row->user->phone ?? 'N/A')
                ->addColumn('week', fn($row) => $row->weeklyDraw ? '<span class="badge bg-primary">Week #' . $row->weeklyDraw->week_number . '</span>' : '-')
                ->addColumn('type', fn($row) => $row->is_rollover ? '<span class="badge bg-secondary">Rollover</span>' : '<span class="badge bg-info">New Entry</span>')
                ->addColumn('amount', fn($row) => '<span class="badge bg-success">$' . number_format($row->donation->amount ?? 0, 2) . '</span>')
                ->addColumn('processing_fee', fn($row) => '<span class="badge bg-secondary">$' . number_format($row->donation->processing_fee ?? 0, 2) . '</span>')
                ->addColumn('total_amount', fn($row) => '<span class="badge bg-info">$' . number_format($row->donation->total_amount ?? 0, 2) . '</span>')
                ->addColumn('is_cover', fn($row) => ($row->donation->is_cover ?? false)
                    ? '<span class="badge bg-success"><i class="fe fe-check" style="font-size:10px"></i> Yes</span>'
                    : '<span class="badge bg-secondary">No</span>'
                )
                ->addColumn('donated_at', fn($row) => $row->donation ? Carbon::parse($row->donation->donated_at)->format('M d, Y h:i A') : 'N/A')
                ->addColumn(
                    'payment_status',
                    fn($row) =>
                    ($row->donation->stripe_payment_status ?? '') === 'completed'
                        ? '<span class="badge bg-success">Paid</span>'
                        : '<span class="badge bg-warning">Pending</span>'
                )
                ->addColumn(
                    'action',
                    fn($row) =>
                    '<button type="button" class="btn btn-sm btn-info viewDonor" data-id="' . $row->donation_id . '" title="View Details">
                        <i class="fe fe-eye" style="font-size: 10px;"></i>
                     </button>'
                )
                ->rawColumns(['week', 'type', 'amount', 'processing_fee', 'total_amount', 'is_cover', 'payment_status', 'action'])
                ->make(true);
        }

        $stats = [
            'total_donors' => Donation::distinct('user_id')->count('user_id'),
            'total_donations' => Donation::count(),
            'total_amount' => Donation::sum('amount'),
            'paid_amount' => Donation::where('stripe_payment_status', 'completed')->sum('amount'),
        ];

        $weeks = DB::table('weekly_draws')
            ->select('id', 'week_number')
            ->orderBy('week_number', 'desc')
            ->get();

        return view('backend.layouts.donation_&_draw.donors_index', compact('stats', 'weeks'));
    }

    public function show($id)
    {
        $donation = Donation::with(['user', 'weeklyDraw'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $donation
        ]);
    }

    public function export(Request $request)
    {
        $query = \App\Models\DrawParticipant::with(['user', 'weeklyDraw', 'donation'])
                ->select('draw_participants.*')
                ->join('weekly_draws', 'draw_participants.weekly_draw_id', '=', 'weekly_draws.id')
                ->join('donations', 'draw_participants.donation_id', '=', 'donations.id')
                ->orderBy('weekly_draws.week_number', 'desc')
                ->orderBy('donations.donated_at', 'desc');

        if ($request->filled('week_id')) $query->where('draw_participants.weekly_draw_id', $request->week_id);
        if ($request->filled('participant_type')) {
            $isRollover = $request->participant_type === 'rollover';
            $query->where('draw_participants.is_rollover', $isRollover);
        }
        if ($request->filled('date_from')) $query->whereDate('donations.donated_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('donations.donated_at', '<=', $request->date_to);
        if ($request->filled('min_amount')) $query->where('donations.amount', '>=', $request->min_amount);
        if ($request->filled('max_amount')) $query->where('donations.amount', '<=', $request->max_amount);
        if ($request->filled('payment_status')) $query->where('donations.stripe_payment_status', $request->payment_status);

        $participants = $query->get();

        $filename = 'participants_' . now()->format('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($participants) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Participant ID', 'Name', 'Email', 'Phone', 'Week', 'Type', 'Amount', 'Processing Fee', 'Total Amount', 'Fee Covered', 'Donated At', 'Payment ID', 'Status']);

            foreach ($participants as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->user->name ?? 'N/A',
                    $p->user->email ?? 'N/A',
                    $p->user->phone ?? 'N/A',
                    $p->weeklyDraw ? 'Week #' . $p->weeklyDraw->week_number : 'N/A',
                    $p->is_rollover ? 'Rollover' : 'New Entry',
                    '$' . number_format($p->donation->amount ?? 0, 2),
                    '$' . number_format($p->donation->processing_fee ?? 0, 2),
                    '$' . number_format($p->donation->total_amount ?? 0, 2),
                    ($p->donation->is_cover ?? false) ? 'Yes' : 'No',
                    $p->donation ? Carbon::parse($p->donation->donated_at)->format('Y-m-d H:i:s') : 'N/A',
                    $p->donation->stripe_payment_id ?? 'N/A',
                    ucfirst($p->donation->stripe_payment_status ?? 'N/A'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
