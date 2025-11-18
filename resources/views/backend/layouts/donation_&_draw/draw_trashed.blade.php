@extends('backend.app')

@section('title', 'Trash Bin - Weekly Draws')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title"><i class="fe fe-trash-2 me-2"></i>Trash Bin</h1>
                        <p class="text-muted">Deleted weekly draws - Can be restored or permanently deleted</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('weekly-draws.index') }}" class="btn btn-primary">
                            <i class="fe fe-arrow-left me-2"></i>Back to Draws
                        </a>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="row mb-4">
                    <div class="col-xl-12">
                        <div class="card overflow-hidden sales-card bg-danger-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-3 tx-12 text-white">ITEMS IN TRASH</h6>
                                        <h4 class="tx-20 fw-bold mb-1 text-white">{{ $trashedCount }}</h4>
                                        <p class="mb-0 tx-12 text-white op-7">Deleted Draws</p>
                                    </div>
                                    <span class="float-end my-auto">
                                        <i class="fas fa-trash-restore text-white fs-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Alert -->
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-triangle me-2"></i>
                    <strong>Warning:</strong> Items in trash can be restored. Use "Delete Forever" to permanently remove
                    them from the database.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <!-- Empty Trash Alert -->
                @if ($trashedCount == 0)
                    <div class="alert alert-info text-center py-5">
                        <i class="fe fe-trash-2 fs-1 mb-3"></i>
                        <h4>Trash is Empty</h4>
                        <p class="mb-0">No deleted draws found. All draws are active.</p>
                    </div>
                @endif

                <!-- Trash Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Deleted Weekly Draws</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="trashedTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Week Number</th>
                                                <th>Status</th>
                                                <th>Total Pool</th>
                                                <th>Participants</th>
                                                <th>Deleted At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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

@push('styles')
    <style>
        .btn-action-group {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .sales-card {
            border-radius: 8px;
        }

        .bg-danger-gradient {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let dTable = $('#trashedTable').DataTable({
                order: [
                    [5, 'desc']
                ], // Order by deleted_at
                lengthMenu: [
                    [20, 50, 100],
                    [20, 50, 100]
                ],
                processing: true,
                responsive: true,
                serverSide: true,
                ajax: "{{ route('weekly-draws.trashed') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'week_number'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'total_pool'
                    },
                    {
                        data: 'total_participants'
                    },
                    {
                        data: 'deleted_at'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Restore Draw
            window.restoreDraw = function(id) {
                Swal.fire({
                    title: 'Restore Draw?',
                    text: 'This will restore the draw to active state.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore!',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('weekly-draws.restore', '') }}/" + id,
                            type: 'POST',
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Restored!',
                                        text: res.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    dTable.ajax.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: res.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Something went wrong!'
                                });
                            }
                        });
                    }
                });
            };

            // Force Delete Draw
            window.forceDeleteDraw = function(id) {
                Swal.fire({
                    title: 'Permanent Delete?',
                    html: '<strong class="text-danger">WARNING:</strong> This action cannot be undone!<br>The draw will be permanently deleted from the database.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete Forever!',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    input: 'checkbox',
                    inputValue: 0,
                    inputPlaceholder: 'I understand this is permanent',
                    inputValidator: (result) => {
                        return !result && 'You must confirm to proceed!'
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('weekly-draws.force-delete', '') }}/" + id,
                            type: 'DELETE',
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: res.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    dTable.ajax.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: res.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Something went wrong!'
                                });
                            }
                        });
                    }
                });
            };
        });
    </script>
@endpush
