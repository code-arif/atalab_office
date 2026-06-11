@extends('backend.app')

@section('title', 'Hero section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Hero Section</h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Manage your homepage hero title and slider images.</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Hero section</li>
                        </ol>
                    </div>
                </div>
                {{-- PAGE-HEADER --}}


                {{-- ── Section Title Card ── --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="hero-card">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-type"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Section Title</h5>
                                    <small class="text-muted">The main heading displayed in the hero section.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form class="form-horizontal" method="post"
                                    action="{{ route('cms.home.hero.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row align-items-end g-3">
                                        <div class="col-lg-9 col-md-8">
                                            <label for="title" class="pro-label">Title</label>
                                            <input type="text"
                                                class="pro-input @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter hero section title…" id="title"
                                                value="{{ $data->title ?? (old('title') ?? '') }}">
                                            @error('title')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3 col-md-4">
                                            <button class="pro-btn pro-btn-primary w-100" type="submit">
                                                <i class="fe fe-save me-2"></i>Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Slider Management ── --}}
                <div class="row g-4">

                    {{-- Add New Slider --}}
                    <div class="col-lg-4">
                        <div class="hero-card h-100">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-upload-cloud"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Add New Slider</h5>
                                    <small class="text-muted">Upload an image to the carousel.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form id="sliderForm" enctype="multipart/form-data">
                                    @csrf

                                    {{-- Upload Zone --}}
                                    <div class="upload-zone mb-3" id="uploadZone" onclick="document.getElementById('sliderImage').click()">
                                        <div id="uploadPlaceholder">
                                            <i class="fe fe-image upload-zone-icon"></i>
                                            <p class="upload-zone-text mb-1">Click or drag to upload</p>
                                            <small class="text-muted">Recommended: 1920 × 1080 px</small>
                                        </div>
                                        <div id="imagePreview" class="d-none">
                                            <img src="" id="previewImg" class="upload-preview-img">
                                            <div class="upload-preview-overlay">
                                                <i class="fe fe-refresh-cw me-1"></i> Change image
                                            </div>
                                        </div>
                                        <input type="file" name="image" id="sliderImage"
                                            accept="image/*" required onchange="previewImage(event)"
                                            style="display:none;">
                                    </div>

                                    {{-- Status Toggle --}}
                                    <div class="pro-toggle-row mb-4">
                                        <div>
                                            <span class="pro-label mb-0">Active Status</span>
                                            <small class="d-block text-muted" style="font-size: 11px;">Show this slider on the frontend</small>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input custom-toggle" type="checkbox"
                                                name="status" id="status" value="1" checked>
                                        </div>
                                    </div>

                                    <button type="submit" class="pro-btn pro-btn-primary w-100" id="submitBtn">
                                        <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner"></span>
                                        <i class="fe fe-plus me-2" id="submitIcon"></i>
                                        <span id="submitText">Add Slider</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Slider List --}}
                    <div class="col-lg-8">
                        <div class="hero-card h-100">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-layers"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">
                                        All Sliders
                                        <span class="slider-count-badge">{{ $sliders->count() }}</span>
                                    </h5>
                                    <small class="text-muted">Drag to reorder. Changes save automatically.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                @if ($sliders->isEmpty())
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fe fe-image"></i>
                                        </div>
                                        <h6 class="mt-3 mb-1">No sliders yet</h6>
                                        <p class="text-muted mb-0" style="font-size: 13px;">Upload your first slider image using the form on the left.</p>
                                    </div>
                                @else
                                    <div id="sortable-sliders" class="slider-list">
                                        @foreach ($sliders as $slider)
                                            <div class="slider-item sortable-item" data-id="{{ $slider->id }}">
                                                {{-- Drag Handle --}}
                                                <div class="slider-drag drag-handle" title="Drag to reorder">
                                                    <i class="fe fe-more-vertical"></i>
                                                    <i class="fe fe-more-vertical" style="margin-left: -8px;"></i>
                                                </div>

                                                {{-- Thumbnail --}}
                                                <div class="slider-thumb">
                                                    <img src="{{ asset('/' . $slider->image) }}"
                                                        alt="Slider #{{ $slider->id }}">
                                                </div>

                                                {{-- Info --}}
                                                <div class="slider-info">
                                                    <span class="slider-name">Slide #{{ $slider->id }}</span>
                                                    <small class="text-muted">Order: {{ $slider->order }}</small>
                                                </div>

                                                {{-- Actions --}}
                                                <div class="slider-actions">
                                                    {{-- Status Badge --}}
                                                    <span class="status-label {{ $slider->status ? 'status-active' : 'status-inactive' }}"
                                                        id="status-label-{{ $slider->id }}">
                                                        {{ $slider->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    
                                                    <div class="action-buttons">
                                                        {{-- Status Toggle --}}
                                                        <div class="form-check form-switch mb-0">
                                                            <input
                                                                class="form-check-input status-toggle custom-toggle"
                                                                type="checkbox" data-id="{{ $slider->id }}"
                                                                {{ $slider->status ? 'checked' : '' }}
                                                                style="cursor: pointer;" value="1">
                                                        </div>

                                                        {{-- Delete --}}
                                                        <button class="pro-icon-btn danger delete-slider"
                                                            data-id="{{ $slider->id }}" title="Delete slider">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
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
            box-shadow: 0 0 0 3px rgba(59,91,219,.12);
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
        }
        .pro-icon-btn.danger:hover {
            background: var(--pro-danger-light);
            border-color: var(--pro-danger);
            color: var(--pro-danger);
        }

        /* ── Toggle ── */
        .custom-toggle {
            width: 40px !important;
            height: 20px !important;
            cursor: pointer;
        }
        .custom-toggle:checked {
            background-color: var(--primary-bg-color, #521aac) !important;
            border-color: var(--primary-bg-color, #521aac) !important;
        }
        .custom-toggle:focus {
            box-shadow: 0 0 0 3px rgba(82,26,172,.18) !important;
            border-color: var(--primary-bg-color, #521aac) !important;
        }

        .pro-toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border: 1.5px solid var(--pro-border);
            border-radius: var(--pro-radius-sm);
            background: #fafbfc;
        }

        /* ── Upload Zone ── */
        .upload-zone {
            position: relative;
            border: 2px dashed #d0d5e8;
            border-radius: var(--pro-radius-sm);
            background: #fafbff;
            cursor: pointer;
            transition: all var(--pro-transition);
            overflow: hidden;
            text-align: center;
            padding: 36px 20px;
        }
        .upload-zone:hover {
            border-color: var(--pro-accent);
            background: var(--pro-accent-light);
        }
        .upload-zone-icon {
            font-size: 36px;
            color: #a5b0d0;
            display: block;
            margin-bottom: 8px;
        }
        .upload-zone:hover .upload-zone-icon { color: var(--pro-accent); }
        .upload-zone-text {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--pro-text);
        }
        .upload-preview-img {
            width: 100%; max-height: 180px;
            object-fit: cover;
            border-radius: 6px;
        }
        .upload-preview-overlay {
            position: absolute; inset: 0;
            background: rgba(0,0,0,.45);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600;
            opacity: 0;
            transition: opacity var(--pro-transition);
            border-radius: var(--pro-radius-sm);
        }
        .upload-zone:hover .upload-preview-overlay { opacity: 1; }
        #imagePreview { padding: 0; }

        /* ── Slider Count Badge ── */
        .slider-count-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 22px; height: 22px;
            padding: 0 7px;
            border-radius: 100px;
            background: var(--pro-accent-light);
            color: var(--pro-accent);
            font-size: 11px; font-weight: 700;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* ── Slider List ── */
        .slider-list { display: flex; flex-direction: column; gap: 10px; }

        .slider-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border: 1.5px solid var(--pro-border);
            border-radius: var(--pro-radius-sm);
            background: #fff;
            transition: all var(--pro-transition);
        }
        .slider-item:hover {
            border-color: #c7cde8;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        .slider-drag {
            cursor: grab;
            color: #c0c5d0;
            font-size: 16px;
            display: flex;
            flex-shrink: 0;
            transition: color var(--pro-transition);
        }
        .slider-drag:hover { color: var(--pro-accent); }
        .slider-drag:active { cursor: grabbing; }

        .slider-thumb img {
            width: 72px; height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--pro-border);
            flex-shrink: 0;
        }

        .slider-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .slider-name {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--pro-text);
        }

        .slider-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
            margin-left: auto;
        }
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .action-buttons .form-check.form-switch {
            padding-left: 2.5em;
            margin-bottom: 0;
            min-height: auto;
            display: flex;
            align-items: center;
        }
        .action-buttons .form-check-input {
            margin-top: 0;
            margin-left: -2.5em;
            vertical-align: middle;
        }
        .pro-icon-btn {
            position: relative;
        }

        .status-label {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 100px;
            letter-spacing: .03em;
        }
        .status-active {
            background: #d3f9d8;
            color: var(--pro-success);
        }
        .status-inactive {
            background: #f1f3f5;
            color: var(--pro-muted);
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }
        .empty-state-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: #f1f3f5;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 28px;
            color: #adb5bd;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        // ── Image Preview ──
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            const previewImg = document.getElementById('previewImg');
            const zone = document.getElementById('uploadZone');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('d-none');
                    placeholder.classList.add('d-none');
                    zone.style.padding = '0';
                }
                reader.readAsDataURL(file);
            }
        }

        // ── Add Slider Form Submit ──
        document.getElementById('sliderForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('submitSpinner');
            const submitText = document.getElementById('submitText');
            const submitIcon = document.getElementById('submitIcon');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitIcon.classList.add('d-none');
            submitText.textContent = 'Adding…';

            const formData = new FormData(this);

            try {
                const response = await axios.post('{{ route('cms.slider.store') }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });

                iziToast.success({
                    title: 'Success',
                    message: response.data.message,
                    position: 'topRight'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1000);

            } catch (error) {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                submitIcon.classList.remove('d-none');
                submitText.textContent = 'Add Slider';

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let errorMessage = '';
                    for (const [field, messages] of Object.entries(errors)) {
                        errorMessage += messages.join('<br>') + '<br>';
                    }
                    iziToast.error({
                        title: 'Validation Error',
                        message: errorMessage,
                        position: 'topRight',
                        timeout: 5000
                    });
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: error.response?.data?.message || 'Failed to add slider.',
                        position: 'topRight'
                    });
                }
            }
        });

        // ── Status Toggle ──
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', async function() {
                const id = this.getAttribute('data-id');
                const newStatus = this.checked;
                const statusText = newStatus ? 'activate' : 'deactivate';

                // Revert until confirmed
                this.checked = !newStatus;

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to ${statusText} this slider?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#521aac',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${statusText} it!`,
                    cancelButtonText: 'Cancel'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const url = "{{ route('cms.slider.status', ':id') }}".replace(':id', id);

                            const response = await axios.post(url, {
                                status: newStatus ? 1 : 0
                            }, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                    'Content-Type': 'application/json'
                                }
                            });

                            this.checked = newStatus;

                            // Update status badge
                            const label = document.getElementById('status-label-' + id);
                            if (label) {
                                label.textContent = newStatus ? 'Active' : 'Inactive';
                                label.className = 'status-label ' + (newStatus ? 'status-active' : 'status-inactive');
                            }

                            Swal.fire({
                                title: 'Updated!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonColor: '#521aac',
                                timer: 2000,
                                showConfirmButton: false
                            });

                        } catch (error) {
                            this.checked = !newStatus;

                            Swal.fire({
                                title: 'Error!',
                                text: error.response?.data?.message || 'Failed to update status.',
                                icon: 'error',
                                confirmButtonColor: '#521aac'
                            });
                        }
                    }
                });
            });
        });

        // ── Delete Slider ──
        document.querySelectorAll('.delete-slider').forEach(button => {
            button.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Delete Slider?',
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e03131',
                    cancelButtonColor: '#521aac',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const url = "{{ route('cms.slider.destroy', ':id') }}".replace(':id', id);

                            const response = await axios.delete(url, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                }
                            });

                            Swal.fire({
                                title: 'Deleted!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonColor: '#521aac',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);

                        } catch (error) {
                            Swal.fire({
                                title: 'Error!',
                                text: error.response?.data?.message || 'Failed to delete slider.',
                                icon: 'error',
                                confirmButtonColor: '#521aac'
                            });
                        }
                    }
                });
            });
        });

        // ── Sortable Drag & Drop ──
        const sortableList = document.getElementById('sortable-sliders');
        if (sortableList && sortableList.children.length > 0) {
            new Sortable(sortableList, {
                animation: 180,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: async function(evt) {
                    const orders = [];
                    document.querySelectorAll('.sortable-item').forEach((item, index) => {
                        orders.push({
                            id: item.getAttribute('data-id'),
                            position: index + 1
                        });
                    });

                    try {
                        const response = await axios.post('{{ route('cms.slider.updateOrder') }}', {
                            orders: orders
                        }, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        });

                        iziToast.success({
                            title: 'Order Saved',
                            message: response.data.message,
                            position: 'topRight'
                        });

                    } catch (error) {
                        iziToast.error({
                            title: 'Error',
                            message: 'Failed to update order.',
                            position: 'topRight'
                        });
                    }
                }
            });
        }
    </script>
@endpush
