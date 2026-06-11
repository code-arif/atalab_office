@extends('backend.app')

@section('title', 'Footer Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Footer Management</h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Manage the website's footer information, logo, and social links.</p>
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
                            <div class="hero-card mb-4">
                                <div class="hero-card-header">
                                    <div class="hero-card-header-icon">
                                        <i class="fe fe-image"></i>
                                    </div>
                                    <div>
                                        <h5 class="hero-card-title mb-0">Brand Identity</h5>
                                        <small class="text-muted">Set the footer logo and business details.</small>
                                    </div>
                                </div>
                                <div class="hero-card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="pro-label">Logo</label>
                                            <input type="file" name="logo" id="logoInput" class="pro-input"
                                                accept="image/*" onchange="previewLogo(event)">
                                            <div class="mt-3 p-2 text-center rounded" id="logoPreview" style="background: #fafbfc; border: 1px dashed var(--pro-border);">
                                                @if ($data->logo)
                                                    <img src="{{ asset('/' . $data->logo) }}" class="img-fluid rounded"
                                                        id="logoImage" style="max-height: 80px;">
                                                @else
                                                    <img src="" class="img-fluid rounded d-none" id="logoImage"
                                                        style="max-height: 80px;">
                                                    <span class="text-muted" style="font-size: 12px;">No logo set</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="pro-label">Business Name</label>
                                                    <input type="text" name="business_name"
                                                        value="{{ old('business_name', $data->business_name) }}"
                                                        class="pro-input" placeholder="Enter business name">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="pro-label">Business Slogan</label>
                                                    <input type="text" name="slogan" value="{{ old('slogan', $data->slogan) }}"
                                                        class="pro-input" placeholder="Enter slogan">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <label for="description" class="pro-label">Footer Description</label>
                                            <div class="editor-container">
                                                <textarea name="description" id="summernote" class="form-control" rows="3" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subscribe Section -->
                            <div class="hero-card mb-4">
                                <div class="hero-card-header">
                                    <div class="hero-card-header-icon">
                                        <i class="fe fe-mail"></i>
                                    </div>
                                    <div>
                                        <h5 class="hero-card-title mb-0">Subscribe Form</h5>
                                        <small class="text-muted">Newsletter subscription details.</small>
                                    </div>
                                </div>
                                <div class="hero-card-body">
                                    <div class="mb-3">
                                        <label class="pro-label">Title</label>
                                        <input type="text" name="subscribe_title"
                                            value="{{ old('subscribe_title', $data->subscribe_title) }}"
                                            class="pro-input" placeholder="Enter subscribe title">
                                    </div>
                                    <div class="mb-2">
                                        <label class="pro-label">Description</label>
                                        <textarea name="subscribe_description" rows="3" class="pro-input" placeholder="Enter short description for the newsletter...">{{ old('subscribe_description', $data->subscribe_description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Copyright & Disclaimer -->
                            <div class="hero-card mb-4">
                                <div class="hero-card-header">
                                    <div class="hero-card-header-icon">
                                        <i class="fe fe-file-text"></i>
                                    </div>
                                    <div>
                                        <h5 class="hero-card-title mb-0">Legal Text</h5>
                                        <small class="text-muted">Copyright notice.</small>
                                    </div>
                                </div>
                                <div class="hero-card-body">
                                    <div class="mb-2">
                                        <label class="pro-label">Copyright</label>
                                        <input type="text" name="copyright"
                                            value="{{ old('copyright', $data->copyright) }}" class="pro-input" placeholder="e.g. &copy; 2024 Your Company. All rights reserved.">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-5">

                            <!-- Social Links -->
                            <div class="hero-card mb-4">
                                <div class="hero-card-header justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="hero-card-header-icon">
                                            <i class="fe fe-share-2"></i>
                                        </div>
                                        <div>
                                            <h5 class="hero-card-title mb-0">Social Links</h5>
                                            <small class="text-muted">Manage social media connections.</small>
                                        </div>
                                    </div>
                                    <button type="button" class="pro-btn" style="background: var(--pro-accent-light); color: var(--pro-accent);" onclick="addSocial()">
                                        <i class="fe fe-plus"></i> Add
                                    </button>
                                </div>
                                <div class="hero-card-body" id="social-links-container" style="background: #fafbfc;">
                                    @foreach (old('social_links', $data->social_links ?? []) as $index => $link)
                                        <div class="social-item mb-3 p-3 bg-white rounded" style="border: 1px solid var(--pro-border); box-shadow: var(--pro-shadow);">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-12 col-md-4">
                                                    <label class="pro-label" style="font-size: 11px;">Platform</label>
                                                    <select name="social_links[{{ $index }}][platform]"
                                                        class="pro-input py-1 px-2 text-sm">
                                                        <option value="">Platform</option>
                                                        @foreach (['linkedin', 'tiktok', 'youtube', 'medium', 'facebook', 'instagram', 'twitter', 'x'] as $plat)
                                                            <option value="{{ $plat }}"
                                                                {{ ($link['platform'] ?? '') == $plat ? 'selected' : '' }}>
                                                                {{ ucfirst($plat) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-8">
                                                    <label class="pro-label" style="font-size: 11px;">URL</label>
                                                    <input type="url" name="social_links[{{ $index }}][url]"
                                                        value="{{ $link['url'] ?? '' }}" placeholder="https://..."
                                                        class="pro-input py-1 px-2 text-sm">
                                                </div>
                                                <div class="col-12 col-md-10 mt-2">
                                                    <label class="pro-label" style="font-size: 11px;">Icon</label>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <input type="file" name="social_links[{{ $index }}][icon]"
                                                            class="pro-input py-1 px-2 text-sm" accept="image/*,.svg">
                                                        @if (isset($link['icon']))
                                                            <div style="width: 32px; height: 32px; background: #f4f5f7; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                                <img src="{{ asset('/' . $link['icon']) }}" width="18">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-2 mt-2 text-end d-flex align-items-end justify-content-end h-100">
                                                    <button type="button" class="btn btn-sm btn-light text-danger w-100" style="height: 33px;"
                                                        onclick="this.closest('.social-item').remove()">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(empty($data->social_links))
                                        <div class="text-center py-4 text-muted" id="emptySocialState">
                                            <i class="fe fe-inbox fs-4 mb-2 d-block text-black-50"></i>
                                            <p class="mb-0" style="font-size: 13px;">No social links added yet.<br>Click the Add button to create one.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="hero-card mb-4" style="border-top: 3px solid var(--pro-accent);">
                                <div class="hero-card-body text-center p-4">
                                    <h5 class="mb-3" style="font-weight: 600;">Ready to go?</h5>
                                    <button type="submit" class="pro-btn pro-btn-primary w-100 py-3" style="font-size: 15px;">
                                        <span class="spinner-border spinner-border-sm d-none me-2" id="submitSpinner"></span>
                                        <i class="fe fe-check-circle me-2" id="submitIcon"></i>
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
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        let socialIndex = {{ count(old('social_links', $data->social_links ?? [])) }};


        // Logo Preview Function
        function previewLogo(event) {
            const file = event.target.files[0];
            const logoImage = document.getElementById('logoImage');
            const placeholder = logoImage.nextElementSibling; // The "No logo set" span

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoImage.src = e.target.result;
                    logoImage.classList.remove('d-none');
                    if(placeholder) placeholder.classList.add('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        function addSocial() {
            const container = document.getElementById('social-links-container');
            const emptyState = document.getElementById('emptySocialState');
            if(emptyState) emptyState.remove();

            const html = `
            <div class="social-item mb-3 p-3 bg-white rounded" style="border: 1px solid var(--pro-border); box-shadow: var(--pro-shadow);">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="pro-label" style="font-size: 11px;">Platform</label>
                        <select name="social_links[${socialIndex}][platform]" class="pro-input py-1 px-2 text-sm">
                            <option value="">Platform</option>
                            @foreach (['linkedin', 'tiktok', 'youtube', 'medium', 'facebook', 'instagram', 'twitter', 'x'] as $plat)
                                <option value="{{ $plat }}">{{ ucfirst($plat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="pro-label" style="font-size: 11px;">URL</label>
                        <input type="url" name="social_links[${socialIndex}][url]" placeholder="https://..." class="pro-input py-1 px-2 text-sm">
                    </div>
                    <div class="col-12 col-md-10 mt-2">
                        <label class="pro-label" style="font-size: 11px;">Icon</label>
                        <input type="file" name="social_links[${socialIndex}][icon]" class="pro-input py-1 px-2 text-sm" accept="image/*,.svg">
                    </div>

                    <div class="col-12 col-md-2 mt-2 text-end d-flex align-items-end justify-content-end h-100">
                        <button type="button" class="btn btn-sm btn-light text-danger w-100" style="height: 33px;"
                        onclick="this.closest('.social-item').remove()">
                            <i class="fe fe-trash-2"></i>
                        </button>
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
            const submitIcon = document.getElementById('submitIcon');

            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitIcon.classList.add('d-none');
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
                submitIcon.classList.remove('d-none');
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

        // Summernote initialization
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Enter section description...',
                tabsize: 2,
                height: 200,
                disableDragAndDrop: false,

                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endpush


@push('styles')
    <!-- Summernote Lite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

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
        .hero-card-body { padding: 30px; }

        /* ── Form Controls ── */
        .pro-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--pro-text);
            margin-bottom: 8px;
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
        textarea.pro-input {
            resize: vertical;
            line-height: 1.5;
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

        /* ── Clean Summernote Overrides ── */
        .editor-container {
            border-radius: var(--pro-radius-sm);
            overflow: hidden;
            border: 1.5px solid var(--pro-border);
            transition: border-color var(--pro-transition);
        }
        .editor-container:focus-within {
            border-color: var(--pro-accent);
        }
        .editor-container.is-invalid-editor {
            border-color: var(--pro-danger);
        }
        .note-editor.note-frame {
            border: none !important;
            border-radius: 0 !important;
            margin-bottom: 0 !important;
            box-shadow: none !important;
        }
        .note-editor .note-toolbar {
            background-color: #fafbfc !important;
            border-bottom: 1px solid var(--pro-border) !important;
            padding: 10px 10px 5px 10px !important;
        }
        .note-editor .note-statusbar {
            background-color: #fafbfc !important;
            border-top: 1px solid var(--pro-border) !important;
        }
        .note-btn {
            border-radius: 6px !important;
            border: 1px solid transparent !important;
            background: transparent !important;
            color: var(--pro-text) !important;
            box-shadow: none !important;
        }
        .note-btn:hover {
            background: rgba(0,0,0,.05) !important;
            border-color: var(--pro-border) !important;
        }
        .note-editor .note-editing-area .note-editable {
            padding: 20px !important;
            color: var(--pro-text) !important;
            font-size: 14px;
        }
        .note-editor .note-editing-area .note-placeholder {
            padding: 20px !important;
            font-size: 14px;
            color: var(--pro-muted) !important;
        }
    </style>
@endpush
