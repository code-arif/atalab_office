@extends('backend.app')

@section('title', 'Hero section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="page-title fw-bold text-dark">Hero Section Management</h1>
                        <p class="text-muted mb-0">Configure the main headline and rotating sliders on the homepage hero area</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb bg-white p-2 rounded-3 shadow-sm">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-primary"><i class="fe fe-home"></i> CMS Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Hero Section</li>
                        </ol>
                    </div>
                </div>
                {{-- PAGE-HEADER END --}}

                <div class="row g-4">
                    {{-- HEADER TEXT --}}
                    <div class="col-12">
                        <div class="card pro-card border-top-primary">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3 d-flex align-items-center">
                                    <div class="bg-primary-transparent p-2 rounded me-2">
                                        <i class="fe fe-type text-primary fs-5"></i>
                                    </div>
                                    Main Headline
                                </h5>
                                <form method="post" action="{{ route('cms.home.hero.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="d-flex align-items-end gap-3">
                                        <div class="flex-grow-1">
                                            <input type="text" class="form-control form-control-lg bg-light border-0 fw-medium @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter the main title text to be displayed prominently on the hero section..." id="title"
                                                value="{{ $data->title ?? (old('title') ?? '') }}">
                                            @error('title')
                                                <span class="text-danger small mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <button class="btn btn-primary btn-lg px-5 shadow-sm" type="submit">
                                            <i class="fe fe-save me-2"></i> Save Headline
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ADD NEW SLIDER --}}
                    <div class="col-xl-4 col-lg-5">
                        <div class="card pro-card h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 d-flex align-items-center">
                                    <div class="bg-success-transparent p-2 rounded me-2">
                                        <i class="fe fe-plus-square text-success fs-5"></i>
                                    </div>
                                    Add New Slider
                                </h5>
                                <form id="sliderForm" enctype="multipart/form-data">
                                    @csrf

                                    <div class="mb-4">
                                        <div class="upload-zone position-relative" onclick="document.getElementById('sliderImage').click()">
                                            <div id="uploadPlaceholder">
                                                <div class="mb-2">
                                                    <i class="fe fe-upload-cloud fs-1 text-muted"></i>
                                                </div>
                                                <h6 class="fw-semibold text-dark mb-1">Click to Upload Image</h6>
                                                <p class="text-muted small mb-0">Recommended size: 1920x1080px (Max 2MB)</p>
                                            </div>
                                            <!-- Image Preview -->
                                            <div id="imagePreview" class="d-none">
                                                <img src="" id="previewImg" class="rounded w-100 object-fit-cover" style="height: 160px;">
                                                <span class="badge bg-dark position-absolute top-0 end-0 m-2" style="cursor:pointer;" onclick="event.stopPropagation(); resetUpload()">Change</span>
                                            </div>
                                        </div>
                                        <input type="file" name="image" id="sliderImage" class="d-none" accept="image/*" required onchange="previewImage(event)">
                                    </div>

                                    <div class="bg-light rounded p-3 mb-4 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">Active Status</h6>
                                            <small class="text-muted">Display immediately after adding</small>
                                        </div>
                                        <div class="form-check form-switch m-0 pb-1">
                                            <input class="form-check-input custom-toggle" type="checkbox" name="status" id="status" value="1" checked>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-dark w-100 btn-lg shadow-sm">
                                        <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner"></span>
                                        <span id="submitText"><i class="fe fe-upload me-2"></i> Upload Slider</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- SLIDERS LIST --}}
                    <div class="col-xl-8 col-lg-7">
                        <div class="card pro-card h-100">
                            <div class="card-header bg-white border-bottom py-4 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 d-flex align-items-center">
                                    <div class="bg-info-transparent p-2 rounded me-2">
                                        <i class="fe fe-layers text-info fs-5"></i>
                                    </div>
                                    Manage Sliders <span class="badge bg-light text-dark ms-2">{{ $sliders->count() }} Total</span>
                                </h5>
                                <span class="text-muted small"><i class="fe fe-move me-1"></i> Drag to reorder</span>
                            </div>
                            <div class="card-body p-4 bg-light bg-opacity-50">
                                @if ($sliders->isEmpty())
                                    <div class="text-center py-5">
                                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center p-4 mb-3 shadow-sm" style="width: 100px; height: 100px;">
                                            <i class="fe fe-image text-muted" style="font-size: 40px;"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark">No sliders found</h5>
                                        <p class="text-muted">Add your first beautiful image slider using the form on the left.</p>
                                    </div>
                                @else
                                    <div id="sortable-sliders" class="row g-3">
                                        @foreach ($sliders as $slider)
                                            <div class="col-md-6 col-xxl-4 sortable-item" data-id="{{ $slider->id }}">
                                                <div class="card slider-item-card border-0 m-0">
                                                    <div class="slider-img-wrapper">
                                                        <div class="drag-handle text-muted"><i class="fe fe-move"></i></div>
                                                        <img src="{{ asset('/' . $slider->image) }}" alt="Slider {{ $slider->id }}">
                                                    </div>
                                                    <div class="slider-actions">
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold text-dark">#{{ $slider->id }}</span>
                                                            <span class="text-muted small">Order: {{ $slider->order }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="form-check form-switch m-0 pt-1">
                                                                <input class="form-check-input status-toggle custom-toggle" type="checkbox" data-id="{{ $slider->id }}" {{ $slider->status ? 'checked' : '' }} value="1">
                                                            </div>
                                                            <button class="btn btn-sm btn-light text-danger delete-slider border" data-id="{{ $slider->id }}" title="Delete Slider">
                                                                <i class="fe fe-trash-2"></i>
                                                            </button>
                                                        </div>
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
        .pro-card {
            border-radius: 12px;
            border: 1px solid #e9edf4;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            background-color: #ffffff;
        }
        .border-top-primary {
            border-top: 4px solid #521aac !important;
        }
        
        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .upload-zone:hover {
            border-color: #521aac;
            background: #f1f5f9;
        }
        
        .slider-item-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
        }
        .slider-item-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            border-color: #e2e8f0;
        }
        .slider-img-wrapper {
            height: 140px;
            width: 100%;
            overflow: hidden;
            position: relative;
            background: #e2e8f0;
        }
        .slider-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .slider-actions {
            background: #fff;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .drag-handle {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            cursor: grab;
            z-index: 10;
            transition: all 0.2s ease;
        }
        .drag-handle:hover {
            background: #521aac;
            color: white !important;
        }
        .drag-handle:active {
            cursor: grabbing;
        }

        /* Custom Toggle Switch Styling */
        .custom-toggle {
            width: 44px !important;
            height: 22px !important;
            cursor: pointer;
        }

        .custom-toggle:checked {
            background-color: #521aac !important;
            border-color: #521aac !important;
        }

        .custom-toggle:focus {
            box-shadow: 0 0 0 0.25rem rgba(82, 26, 172, 0.25) !important;
            border-color: #521aac !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        // Image Preview and Dropzone
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            const placeholder = document.getElementById('uploadPlaceholder');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    placeholder.classList.add('d-none');
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        function resetUpload() {
            document.getElementById('sliderImage').value = '';
            document.getElementById('imagePreview').classList.add('d-none');
            document.getElementById('uploadPlaceholder').classList.remove('d-none');
        }

        // Add Slider Form Submit
        document.getElementById('sliderForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('submitSpinner');
            const submitText = document.getElementById('submitText');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitText.textContent = ' Adding...';

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

        // Status Toggle with SweetAlert Confirmation
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', async function() {
                const id = this.getAttribute('data-id');
                const newStatus = this.checked;
                const statusText = newStatus ? 'activate' : 'deactivate';

                // Prevent toggle until confirmed
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
                            const url = "{{ route('cms.slider.status', ':id') }}".replace(
                                ':id', id);

                            const response = await axios.post(url, {
                                status: newStatus ? 1 : 0
                            }, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'input[name="_token"]').value,
                                    'Content-Type': 'application/json'
                                }
                            });

                            // Update toggle to new status
                            this.checked = newStatus;

                            Swal.fire({
                                title: 'Success!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonColor: '#521aac',
                                timer: 2000,
                                showConfirmButton: false
                            });

                        } catch (error) {
                            // Keep toggle at old status
                            this.checked = !newStatus;

                            Swal.fire({
                                title: 'Error!',
                                text: error.response?.data?.message ||
                                    'Failed to update status.',
                                icon: 'error',
                                confirmButtonColor: '#521aac'
                            });
                        }
                    }
                    // If cancelled, keep toggle at old status (already set above)
                });
            });
        });

        // Delete Slider with SweetAlert
        document.querySelectorAll('.delete-slider').forEach(button => {
            button.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#521aac',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const url = "{{ route('cms.slider.destroy', ':id') }}".replace(
                                ':id', id);

                            const response = await axios.delete(url, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'input[name="_token"]').value
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
                                text: error.response?.data?.message ||
                                    'Failed to delete slider.',
                                icon: 'error',
                                confirmButtonColor: '#521aac'
                            });
                        }
                    }
                });
            });
        });

        // Sortable (Drag & Drop)
        const sortableList = document.getElementById('sortable-sliders');
        if (sortableList && sortableList.children.length > 0) {
            new Sortable(sortableList, {
                animation: 150,
                handle: '.drag-handle',
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
                            title: 'Success',
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
