@extends('backend.app')

@section('title', 'Distribution section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Home page - Distribution section</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Distribution section</li>
                        </ol>
                    </div>
                </div>

                {{-- PAGE-CONTENT --}}
                <div class="row">
                    {{-- distribution header and description --}}
                    <div class="col-lg-4">
                        <div class="card box-shadow-0">
                            <div class="card-header bg-light">
                                <h4 class="card-title">Header & Description</h4>
                            </div>
                            <div class="card-body">
                                <form class="" method="post"
                                    action="{{ route('cms.home.distribution.section.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        {{-- Title --}}
                                        <div class="form-group mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter title" id="title"
                                                value="{{ $data->title ?? old('title') }}">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Description --}}
                                        <div class="form-group mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="4" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Submit --}}
                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">Save changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Add New Draw Setting Form --}}
                    <div class="col-lg-8">
                        <div class="card box-shadow-0">
                            <div class="card-header bg-light">
                                <h4 class="card-title">Add New Draw Setting</h4>
                            </div>
                            <div class="card-body">
                                <form class="form-horizontal" method="post"
                                    action="{{ route('cms.home.distribution.item.store') }}">
                                    @csrf

                                    <div class="row">
                                        {{-- Participants --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Participants</label>
                                            <input type="number"
                                                class="form-control @error('participants') is-invalid @enderror"
                                                name="participants" placeholder="Enter participants"
                                                value="{{ old('participants') }}">
                                            @error('participants')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Total Pool --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Total Pool ($)</label>
                                            <input type="number" step="0.01"
                                                class="form-control @error('total_pool') is-invalid @enderror"
                                                name="total_pool" placeholder="Enter total pool"
                                                value="{{ old('total_pool') }}">
                                            @error('total_pool')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Recipients --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Recipients</label>
                                            <input type="number"
                                                class="form-control @error('recipients') is-invalid @enderror"
                                                name="recipients" placeholder="Enter recipients"
                                                value="{{ old('recipients') }}">
                                            @error('recipients')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Odds Numerator --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Odds Numerator</label>
                                            <input type="number"
                                                class="form-control @error('odds_numerator') is-invalid @enderror"
                                                name="odds_numerator" placeholder="1"
                                                value="{{ old('odds_numerator', 1) }}">
                                            @error('odds_numerator')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Odds Denominator --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Odds Denominator</label>
                                            <input type="number"
                                                class="form-control @error('odds_denominator') is-invalid @enderror"
                                                name="odds_denominator" placeholder="400"
                                                value="{{ old('odds_denominator', 400) }}">
                                            @error('odds_denominator')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Net Per Recipient --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Net Per Recipient ($)</label>
                                            <input type="number" step="0.01"
                                                class="form-control @error('net_per_recipient') is-invalid @enderror"
                                                name="net_per_recipient" placeholder="Enter amount"
                                                value="{{ old('net_per_recipient') }}">
                                            @error('net_per_recipient')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fa fa-plus"></i> Add Draw Setting
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Draw Settings Table --}}
                    <div class="col-lg-12">
                        <div class="card box-shadow-0">
                            <div class="card-header">
                                <h4 class="card-title">All Draw Settings</h4>
                            </div>
                            <div class="card-body">
                                @if ($drawSettings->isEmpty())
                                    <div class="alert alert-info">
                                        No draw settings found. Please add a new draw setting above.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover text-nowrap">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Participants</th>
                                                    <th>Total Pool</th>
                                                    <th>Recipients</th>
                                                    <th>Odds</th>
                                                    <th>Net Per Recipient</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($drawSettings as $index => $setting)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ number_format($setting->participants) }}</td>
                                                        <td>${{ number_format($setting->total_pool, 2) }}</td>
                                                        <td>{{ number_format($setting->recipients) }}</td>
                                                        <td>{{ $setting->odds_numerator }}:{{ $setting->odds_denominator }}
                                                        </td>
                                                        <td>${{ number_format($setting->net_per_recipient, 2) }}</td>
                                                        <td>{{ $setting->created_at->format('M d, Y') }}</td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editModal{{ $setting->id }}">
                                                                <i class="fa fa-edit"></i> Edit
                                                            </button>

                                                            <form id="deleteForm{{ $setting->id }}"
                                                                action="{{ route('cms.home.distribution.item.delete', $setting->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger delete-btn"
                                                                    data-form-id="deleteForm{{ $setting->id }}">
                                                                    <i class="fa fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>

                                                    {{-- Edit Modal --}}
                                                    <div class="modal fade" id="editModal{{ $setting->id }}"
                                                        tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Draw Setting</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST"
                                                                    action="{{ route('cms.home.distribution.item.update', $setting->id) }}">
                                                                    @csrf
                                                                    @method('POST')
                                                                    <div class="modal-body">
                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-3">
                                                                                <label
                                                                                    class="form-label">Participants</label>
                                                                                <input type="number" class="form-control"
                                                                                    name="participants"
                                                                                    value="{{ $setting->participants }}"
                                                                                    required>
                                                                            </div>

                                                                            <div class="col-md-6 mb-3">
                                                                                <label class="form-label">Total Pool
                                                                                    ($)
                                                                                </label>
                                                                                <input type="number" step="0.01"
                                                                                    class="form-control" name="total_pool"
                                                                                    value="{{ $setting->total_pool }}"
                                                                                    required>
                                                                            </div>

                                                                            <div class="col-md-6 mb-3">
                                                                                <label
                                                                                    class="form-label">Recipients</label>
                                                                                <input type="number" class="form-control"
                                                                                    name="recipients"
                                                                                    value="{{ $setting->recipients }}"
                                                                                    required>
                                                                            </div>

                                                                            <div class="col-md-6 mb-3">
                                                                                <label class="form-label">Net Per Recipient
                                                                                    ($)</label>
                                                                                <input type="number" step="0.01"
                                                                                    class="form-control"
                                                                                    name="net_per_recipient"
                                                                                    value="{{ $setting->net_per_recipient }}"
                                                                                    required>
                                                                            </div>

                                                                            <div class="col-md-6 mb-3">
                                                                                <label class="form-label">Odds
                                                                                    Numerator</label>
                                                                                <input type="number" class="form-control"
                                                                                    name="odds_numerator"
                                                                                    value="{{ $setting->odds_numerator }}"
                                                                                    required>
                                                                            </div>

                                                                            <div class="col-md-6 mb-3">
                                                                                <label class="form-label">Odds
                                                                                    Denominator</label>
                                                                                <input type="number" class="form-control"
                                                                                    name="odds_denominator"
                                                                                    value="{{ $setting->odds_denominator }}"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit"
                                                                            class="btn btn-primary">Update Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
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
        // SweetAlert2 Delete Confirmation
        document.addEventListener('DOMContentLoaded', function() {
            // Get all delete buttons
            const deleteButtons = document.querySelectorAll('.delete-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const formId = this.getAttribute('data-form-id');
                    const form = document.getElementById(formId);

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });

        // Show success message with SweetAlert2
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
    </script>
@endpush
