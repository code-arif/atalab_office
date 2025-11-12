@extends('backend.app')

@section('title', 'Footer Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Footer Management</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">CMS</a></li>
                            <li class="breadcrumb-item active">Footer</li>
                        </ol>
                    </div>
                </div>

                <form id="footerForm" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-lg-7">
                            <!-- Logo & Slogan -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h3 class="card-title">Logo</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Logo</label>
                                            <input type="file" name="logo" id="logoInput" class="form-control"
                                                accept="image/*" onchange="previewLogo(event)">
                                            <div class="mt-2" id="logoPreview">
                                                @if ($data->logo)
                                                    <img src="{{ asset('/' . $data->logo) }}" class="img-thumbnail"
                                                        id="logoImage" width="80">
                                                @else
                                                    <img src="" class="img-thumbnail d-none" id="logoImage"
                                                        width="80">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Business</label>
                                            <input type="text" name="business_name"
                                                value="{{ old('business_name', $data->business_name) }}"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Business Slogan</label>
                                            <input type="text" name="slogan" value="{{ old('slogan', $data->slogan) }}"
                                                class="form-control">
                                        </div>

                                        <div class="form-group mb-3 mt-3">
                                            <label for="description" class="form-label">Footer Description</label>
                                            <textarea name="description" id="summernote" class="form-control" rows="3" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subscribe Section -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h3 class="card-title">Subscribe Form</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label>Title</label>
                                        <input type="text" name="subscribe_title"
                                            value="{{ old('subscribe_title', $data->subscribe_title) }}"
                                            class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label>Description</label>
                                        <textarea name="subscribe_description" rows="3" class="form-control">{{ old('subscribe_description', $data->subscribe_description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Copyright & Disclaimer -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h3 class="card-title">Legal Text</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label>Copyright</label>
                                        <input type="text" name="copyright"
                                            value="{{ old('copyright', $data->copyright) }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-5">

                            <!-- Social Links -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between bg-light">
                                    <h3 class="card-title">Social Links</h3>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="addSocial()">Add</button>
                                </div>
                                <div class="card-body" id="social-links-container">
                                    @foreach (old('social_links', $data->social_links ?? []) as $index => $link)
                                        <div class="social-item mb-3 p-3 border">
                                            <div class="row g-1">
                                                <div class="col-12 col-md-3">
                                                    <select name="social_links[{{ $index }}][platform]"
                                                        class="form-control form-control-sm">
                                                        <option value="">Platform</option>
                                                        @foreach (['linkedin', 'tiktok', 'youtube', 'medium', 'facebook', 'instagram', 'twitter', 'x'] as $plat)
                                                            <option value="{{ $plat }}"
                                                                {{ ($link['platform'] ?? '') == $plat ? 'selected' : '' }}>
                                                                {{ ucfirst($plat) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <input type="url" name="social_links[{{ $index }}][url]"
                                                        value="{{ $link['url'] ?? '' }}" placeholder="https://..."
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <input type="file" name="social_links[{{ $index }}][icon]"
                                                        class="form-control form-control-sm" accept="image/*,.svg">
                                                    @if (isset($link['icon']))
                                                        <img src="{{ asset('/' . $link['icon']) }}" width="24"
                                                            class="mt-1">
                                                    @endif
                                                </div>

                                                <div class="col-12 col-md-1">
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="this.closest('.social-item').remove()">×</button>
                                                </div>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="card mt-4">
                                <div class="card-body text-center">
                                    <button type="submit" class="btn btn-success btn w-100">
                                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                                        <span id="submitText">Update Footer</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">

    <script>
        let socialIndex = {{ count(old('social_links', $data->social_links ?? [])) }};


        // Logo Preview Function
        function previewLogo(event) {
            const file = event.target.files[0];
            const logoImage = document.getElementById('logoImage');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoImage.src = e.target.result;
                    logoImage.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        function addSocial() {
            const container = document.getElementById('social-links-container');
            const html = `
            <div class="social-item mb-3 p-3 border">
                <div class="row g-1">
                    <div class="col-12 col-md-3">
                        <select name="social_links[${socialIndex}][platform]" class="form-control form-control-sm">
                            <option value="">Platform</option>
                            @foreach (['linkedin', 'tiktok', 'youtube', 'medium', 'facebook', 'instagram', 'twitter', 'x'] as $plat)
                                <option value="{{ $plat }}">{{ ucfirst($plat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <input type="url" name="social_links[${socialIndex}][url]" placeholder="https://..." class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-md-4">
                        <input type="file" name="social_links[${socialIndex}][icon]" class="form-control form-control-sm" accept="image/*,.svg">
                    </div>

                    <div class="col-12 col-md-1">
                        <button type="button" class="btn btn-danger btn-sm"
                        onclick="this.closest('.social-item').remove()">×</button>
                    </div>
                </div>

            </div>`;
            container.insertAdjacentHTML('beforeend', html);
            socialIndex++;
        }

        // Form submission with Axios
        document.getElementById('footerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('submitSpinner');
            const submitText = document.getElementById('submitText');

            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitText.textContent = ' Updating...';

            const formData = new FormData(this);

            try {
                const response = await axios.post('{{ route('cms.footer.section.update') }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });

                iziToast.success({
                    title: 'Success',
                    message: response.data.message || 'Footer updated successfully!',
                    position: 'topRight'
                });

                // Reload page after 1 second to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);

            } catch (error) {
                // Hide loading state
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                submitText.textContent = 'Update Footer';

                if (error.response) {
                    // Validation errors
                    if (error.response.status === 422) {
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
                        // Other errors
                        iziToast.error({
                            title: 'Error',
                            message: error.response.data.message ||
                                'Failed to update footer. Please try again.',
                            position: 'topRight'
                        });
                    }
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: 'Network error. Please check your connection.',
                        position: 'topRight'
                    });
                }
            }
        });
    </script>
@endpush
