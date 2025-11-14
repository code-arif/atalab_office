<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class ContactUsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $queries = ContactUs::select(['id', 'name', 'email', 'phone', 'message', 'is_read', 'created_at', 'read_at']);

            return DataTables::of($queries)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return $row->created_at
                        ? $row->created_at->format('d M, Y h:i A')
                        : 'N/A';
                })
                ->editColumn(
                    'is_read',
                    fn($row) => $row->is_read
                        ? '<span class="badge bg-success">Read</span>'
                        : '<span class="badge bg-warning">Unread</span>'
                )
                ->addColumn('action', function ($row) {
                    $viewBtn = '<button class="btn btn-sm btn-info view-btn me-1" data-id="' . $row->id . '"><i class="fe fe-eye"></i></button>';
                    $deleteBtn = '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fe fe-trash"></i></button>';
                    return '<div class="btn-list">' . $viewBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['is_read', 'action'])
                ->make(true);
        }

        return view('backend.layouts.contact.index');
    }


    // Show single query via AJAX for modal
    public function show($id)
    {
        $contactUs = ContactUs::findOrFail($id);

        if (!$contactUs->is_read) {
            $contactUs->is_read = true;
            $contactUs->read_at = now();
            $contactUs->save();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'name'    => $contactUs->name,
                'email'   => $contactUs->email,
                'phone'   => $contactUs->phone ?? 'N/A',
                'message' => nl2br(e($contactUs->message)),
                'sent_at' => $contactUs->created_at?->format('d M, Y h:i A') ?? 'N/A',
                'read_at' => $contactUs->read_at?->format('d M, Y h:i A') ?? 'Not read yet',
            ]
        ]);
    }


    // Delete
    public function destroy($id)
    {
        $contactUs = ContactUs::findOrFail($id);
        $contactUs->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully!'
        ]);
    }
}
