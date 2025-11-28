<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use Carbon\Carbon;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Donation::with(['user:id,name,email,phone,donor_id', 'weeklyDraw:id,week_number'])
                ->select('donations.*');

            // Filters
            if ($request->filled('week_id')) {
                $query->where('week_id', $request->week_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('donated_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('donated_at', '<=', $request->date_to);
            }

            if ($request->filled('min_amount')) {
                $query->where('amount', '>=', $request->min_amount);
            }

            if ($request->filled('max_amount')) {
                $query->where('amount', '<=', $request->max_amount);
            }

            if ($request->filled('payment_status')) {
                $query->where('stripe_payment_status', $request->payment_status);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('donor_name', fn($row) => $row->user->name ?? 'N/A')
                ->addColumn('donor_id', fn($row) => $row->user->donor_id ?? 'N/A')
                ->addColumn('email', fn($row) => $row->user->email ?? 'N/A')
                ->addColumn('phone', fn($row) => $row->user->phone ?? 'N/A')
                ->addColumn('week', fn($row) => $row->weeklyDraw ? '<span class="badge bg-primary">Week #' . $row->weeklyDraw->week_number . '</span>' : '-')
                ->addColumn('amount', fn($row) => '<span class="badge bg-success">$' . number_format($row->amount, 2) . '</span>')
                ->addColumn('donated_at', fn($row) => Carbon::parse($row->donated_at)->format('M d, Y h:i A'))
                ->addColumn(
                    'payment_status',
                    fn($row) =>
                    $row->stripe_payment_status === 'completed'
                        ? '<span class="badge bg-success">Paid</span>'
                        : '<span class="badge bg-warning">Pending</span>'
                )
                ->addColumn(
                    'action',
                    fn($row) =>
                    '<button type="button" class="btn btn-sm btn-info viewDonor" data-id="' . $row->id . '" title="View Details">
                        <i class="fe fe-eye" style="font-size: 10px;"></i>
                     </button>'
                )
                ->rawColumns(['week', 'amount', 'payment_status', 'action'])
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
        $query = Donation::with(['user', 'weeklyDraw']);

        // Apply same filters as index
        if ($request->filled('week_id')) $query->where('week_id', $request->week_id);
        if ($request->filled('date_from')) $query->whereDate('donated_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('donated_at', '<=', $request->date_to);
        if ($request->filled('min_amount')) $query->where('amount', '>=', $request->min_amount);
        if ($request->filled('max_amount')) $query->where('amount', '<=', $request->max_amount);

        $donations = $query->get();

        $filename = 'donors_' . now()->format('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($donations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Week', 'Amount', 'Donated At', 'Payment ID', 'Status']);

            foreach ($donations as $d) {
                fputcsv($file, [
                    $d->id,
                    $d->user->name ?? 'N/A',
                    $d->user->email ?? 'N/A',
                    $d->user->phone ?? 'N/A',
                    $d->weeklyDraw ? 'Week #' . $d->weeklyDraw->week_number : 'N/A',
                    '$' . number_format($d->amount, 2),
                    Carbon::parse($d->donated_at)->format('Y-m-d H:i:s'),
                    $d->stripe_payment_id,
                    ucfirst($d->stripe_payment_status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
