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
                        <h1 class="page-title">Distribution Section</h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Manage your distribution header, description, and draw settings.</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Distribution section</li>
                        </ol>
                    </div>
                </div>

                {{-- PAGE-CONTENT --}}
                <div class="row g-4">
                    {{-- distribution header and description --}}
                    <div class="col-lg-4">
                        <div class="hero-card h-100">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-file-text"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Header & Description</h5>
                                    <small class="text-muted">Main text for the distribution area.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form method="post" action="{{ route('cms.home.distribution.section.update') }}" enctype="multipart/form-data">
                                    @csrf

                                    {{-- Title --}}
                                    <div class="mb-3">
                                        <label for="title" class="pro-label">Title</label>
                                        <input type="text" class="pro-input @error('title') is-invalid @enderror"
                                            name="title" placeholder="Enter title" id="title"
                                            value="{{ $data->title ?? old('title') }}">
                                        @error('title')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Description --}}
                                    <div class="mb-4">
                                        <label for="description" class="pro-label">Description</label>
                                        <textarea name="description" class="pro-input" rows="5" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Submit --}}
                                    <button class="pro-btn pro-btn-primary w-100" type="submit">
                                        <i class="fe fe-save me-2"></i>Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Add New Draw Setting Form --}}
                    <div class="col-lg-8">
                        <div class="hero-card h-100">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-plus-circle"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Add New Draw Setting</h5>
                                    <small class="text-muted">Configure a new prize distribution tier.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form class="form-horizontal" method="post" action="{{ route('cms.home.distribution.item.store') }}">
                                    @csrf
                                    <div class="row g-3">
                                        {{-- Participants --}}
                                        <div class="col-md-4">
                                            <label class="pro-label">Participants</label>
                                            <input type="number" class="pro-input @error('participants') is-invalid @enderror"
                                                name="participants" placeholder="e.g. 1000" value="{{ old('participants') }}">
                                            @error('participants')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Total Pool --}}
                                        <div class="col-md-4">
                                            <label class="pro-label">Total Pool ($)</label>
                                            <input type="number" step="0.01" class="pro-input @error('total_pool') is-invalid @enderror"
                                                name="total_pool" placeholder="0.00" value="{{ old('total_pool') }}">
                                            @error('total_pool')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Recipients --}}
                                        <div class="col-md-4">
                                            <label class="pro-label">Recipients</label>
                                            <input type="number" class="pro-input @error('recipients') is-invalid @enderror"
                                                name="recipients" placeholder="e.g. 50" value="{{ old('recipients') }}">
                                            @error('recipients')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Odds Numerator --}}
                                        <div class="col-md-4">
                                            <label class="pro-label">Odds Numerator</label>
                                            <input type="number" class="pro-input @error('odds_numerator') is-invalid @enderror"
                                                name="odds_numerator" placeholder="1" value="{{ old('odds_numerator', 1) }}">
                                            @error('odds_numerator')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Odds Denominator --}}
                                        <div class="col-md-4">
                                            <label class="pro-label">Odds Denominator</label>
                                            <input type="number" class="pro-input @error('odds_denominator') is-invalid @enderror"
                                                name="odds_denominator" placeholder="400" value="{{ old('odds_denominator', 400) }}">
                                            @error('odds_denominator')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Net Per Recipient --}}
                                        <div class="col-md-4">
                                            <label class="pro-label">Net Per Recipient ($)</label>
                                            <input type="number" step="0.01" class="pro-input @error('net_per_recipient') is-invalid @enderror"
                                                name="net_per_recipient" placeholder="0.00" value="{{ old('net_per_recipient') }}">
                                            @error('net_per_recipient')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <button class="pro-btn pro-btn-primary" type="submit">
                                            <i class="fe fe-plus me-2"></i> Add Draw Setting
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Draw Settings Table --}}
                    <div class="col-lg-12">
                        <div class="hero-card">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-list"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">All Draw Settings</h5>
                                    <small class="text-muted">Overview of all configured distributions.</small>
                                </div>
                            </div>
                            <div class="hero-card-body p-0">
                                @if ($drawSettings->isEmpty())
                                    <div class="empty-state py-5">
                                        <div class="empty-state-icon">
                                            <i class="fe fe-inbox"></i>
                                        </div>
                                        <h6 class="mt-3 mb-1">No draw settings found</h6>
                                        <p class="text-muted mb-0" style="font-size: 13px;">Please add a new draw setting above.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table pro-table mb-0 text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th class="ps-4">#</th>
                                                    <th>Participants</th>
                                                    <th>Total Pool</th>
                                                    <th>Recipients</th>
                                                    <th>Odds</th>
                                                    <th>Net Per Recipient</th>
                                                    <th>Created</th>
                                                    <th class="pe-4 text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($drawSettings as $index => $setting)
                                                    <tr>
                                                        <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                        <td class="fw-medium">{{ number_format($setting->participants) }}</td>
                                                        <td class="fw-medium text-success">${{ number_format($setting->total_pool, 2) }}</td>
                                                        <td>{{ number_format($setting->recipients) }}</td>
                                                        <td><span class="badge bg-light text-dark border">{{ $setting->odds_numerator }}:{{ $setting->odds_denominator }}</span></td>
                                                        <td class="fw-medium">${{ number_format($setting->net_per_recipient, 2) }}</td>
                                                        <td class="text-muted small">{{ $setting->created_at->format('M d, Y') }}</td>
                                                        <td class="pe-4 text-end">
                                                            <div class="d-inline-flex gap-2">
                                                                <button type="button" class="pro-icon-btn text-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editModal{{ $setting->id }}" title="Edit">
                                                                    <i class="fe fe-edit-2"></i>
                                                                </button>

                                                                <form id="deleteForm{{ $setting->id }}"
                                                                    action="{{ route('cms.home.distribution.item.delete', $setting->id) }}"
                                                                    method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button"
                                                                        class="pro-icon-btn danger delete-btn"
                                                                        data-form-id="deleteForm{{ $setting->id }}" title="Delete">
                                                                        <i class="fe fe-trash-2"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    {{-- Edit Modal --}}
                                                    <div class="modal fade" id="editModal{{ $setting->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--pro-radius);">
                                                                <div class="modal-header bg-light border-bottom-0" style="border-radius: var(--pro-radius) var(--pro-radius) 0 0;">
                                                                    <h5 class="modal-title fw-bold">Edit Draw Setting</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                                                                </div>
                                                                <form method="POST" action="{{ route('cms.home.distribution.item.update', $setting->id) }}">
                                                                    @csrf
                                                                    @method('POST')
                                                                    <div class="modal-body p-4">
                                                                        <div class="row g-3">
                                                                            <div class="col-md-6">
                                                                                <label class="pro-label">Participants</label>
                                                                                <input type="number" class="pro-input" name="participants" value="{{ $setting->participants }}" required>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="pro-label">Total Pool ($)</label>
                                                                                <input type="number" step="0.01" class="pro-input" name="total_pool" value="{{ $setting->total_pool }}" required>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="pro-label">Recipients</label>
                                                                                <input type="number" class="pro-input" name="recipients" value="{{ $setting->recipients }}" required>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="pro-label">Net Per Recipient ($)</label>
                                                                                <input type="number" step="0.01" class="pro-input" name="net_per_recipient" value="{{ $setting->net_per_recipient }}" required>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="pro-label">Odds Numerator</label>
                                                                                <input type="number" class="pro-input" name="odds_numerator" value="{{ $setting->odds_numerator }}" required>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="pro-label">Odds Denominator</label>
                                                                                <input type="number" class="pro-input" name="odds_denominator" value="{{ $setting->odds_denominator }}" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer border-top-0 bg-light" style="border-radius: 0 0 var(--pro-radius) var(--pro-radius);">
                                                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="pro-btn pro-btn-primary">Save Changes</button>
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

@push('styles')
    <style>
        /* ── Design Tokens ── */
        :root {
            --pro-radius: 12px;
            --pro-radius-sm: 8px;
            --pro-border: #e8eaed;
            --pro-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 8px rgba(0,0,0,.04);
            --pro-shadow-hover: 0 4px 16px rgba(0,0,0,.10);
            --pro-accent: var(--primary-bg-color, #521aac);
            --pro-accent-light: rgba(82, 26, 172, .08);
            --pro-danger: #e03131;
            --pro-danger-light: #fff5f5;
            --pro-success: #2f9e44;
            --pro-text: #1a1d23;
            --pro-muted: #6c757d;
            --pro-transition: .18s ease;
        }

        /* ── Card ── */
        .hero-card {
            background: #fff;
            border: 1px solid var(--pro-border);
            border-radius: var(--pro-radius);
            box-shadow: var(--pro-shadow);
            overflow: hidden;
            transition: box-shadow var(--pro-transition);
        }
        .hero-card:hover { box-shadow: var(--pro-shadow-hover); }

        .hero-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--pro-border);
            background: #fafbfc;
        }
        .hero-card-header-icon {
            width: 38px; height: 38px;
            border-radius: var(--pro-radius-sm);
            background: var(--pro-accent-light);
            color: var(--pro-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .hero-card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--pro-text);
            letter-spacing: -.01em;
        }
        .hero-card-body { padding: 22px; }

        /* ── Form Controls ── */
        .pro-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--pro-text);
            margin-bottom: 6px;
            letter-spacing: -.01em;
        }
        .pro-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--pro-border);
            border-radius: var(--pro-radius-sm);
            font-size: 14px;
            color: var(--pro-text);
            background: #fff;
            transition: border-color var(--pro-transition), box-shadow var(--pro-transition);
            outline: none;
        }
        .pro-input:focus {
            border-color: var(--pro-accent);
            box-shadow: 0 0 0 3px rgba(82,26,172,.18);
        }
        .pro-input.is-invalid { border-color: var(--pro-danger); }
        .invalid-feedback { font-size: 12px; color: var(--pro-danger); margin-top: 4px; }

        /* ── Buttons ── */
        .pro-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 10px 20px;
            border-radius: var(--pro-radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--pro-transition);
            letter-spacing: -.01em;
        }
        .pro-btn-primary {
            background: var(--pro-accent);
            color: #fff;
        }
        .pro-btn-primary:hover {
            filter: brightness(1.12);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(82,26,172,.35);
            color: #fff;
        }
        .pro-btn-primary:active { transform: translateY(0); }
        .pro-btn-primary:disabled { opacity: .65; cursor: not-allowed; transform: none; }

        .pro-icon-btn {
            width: 32px; height: 32px;
            border-radius: var(--pro-radius-sm);
            border: 1.5px solid var(--pro-border);
            background: transparent;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all var(--pro-transition);
            color: var(--pro-muted);
            position: relative;
        }
        .pro-icon-btn:hover {
            background: #f8f9fa;
            color: var(--pro-accent);
            border-color: #d2d6da;
        }
        .pro-icon-btn.danger:hover {
            background: var(--pro-danger-light);
            border-color: var(--pro-danger);
            color: var(--pro-danger);
        }

        /* ── Table ── */
        .pro-table th {
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            color: var(--pro-muted);
            background: #fafbfc;
            border-bottom: 2px solid var(--pro-border);
            padding: 14px 12px;
        }
        .pro-table td {
            vertical-align: middle;
            padding: 16px 12px;
            font-size: 14px;
            border-bottom: 1px solid var(--pro-border);
        }
        .pro-table tbody tr:last-child td { border-bottom: none; }
        .pro-table tbody tr:hover { background: #fafbff; }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
        }
        .empty-state-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: #f1f3f5;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 28px;
            color: #adb5bd;
            margin: 0 auto;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // SweetAlert2 Delete Confirmation
        document.addEventListener('DOMContentLoaded', function() {
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
                        confirmButtonColor: '#e03131',
                        cancelButtonColor: '#521aac',
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
                showConfirmButton: false,
                confirmButtonColor: '#521aac'
            });
        @endif
    </script>
@endpush
