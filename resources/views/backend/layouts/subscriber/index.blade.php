@extends('backend.app')
@section('title', 'Newsletter Subscribers')

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        .user-agent-col {
            max-width: 220px;
            word-break: break-all;
            font-size: 11px;
            line-height: 1.3;
        }

        code {
            background: #f1f3f5;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Newsletter Subscribers</h1>
                        <small class="text-muted">Total: {{ \App\Models\Subscriber::count() }} subscribers</small>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Subscribers</a></li>
                            <li class="breadcrumb-item active">All Subscribers</li>
                        </ol>
                    </div>
                </div>
                <!-- END PAGE HEADER -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">All Subscribers</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="25%">Email</th>
                                                <th width="15%">IP Address</th>
                                                <th width="35%">User Agent</th>
                                                <th width="20%">Subscribed At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- DataTables will fill this -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('subscribers.index') }}",
                    type: "GET"
                },
                order: [
                    [4, 'desc']
                ], // sort by subscribed_at (index 4)
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address',
                        className: 'text-center'
                    },
                    {
                        data: 'user_agent',
                        name: 'user_agent',
                        className: 'user-agent-col'
                    },
                    {
                        data: 'subscribed_at',
                        name: 'subscribed_at'
                    }
                ],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"></div>'
                },
                columnDefs: [{
                        targets: 2,
                        className: 'text-center'
                    }, // IP
                    {
                        targets: 3,
                        className: 'user-agent-col'
                    } // User Agent
                ]
            });
        });
    </script>
@endpush
