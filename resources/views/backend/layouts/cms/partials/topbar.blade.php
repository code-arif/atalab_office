@extends('backend.app')

@section('title', 'Topbar Section Manage')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Topbar Section</h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Manage the website's topbar information.</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Topbar Section</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Index</li>
                        </ol>
                    </div>
                </div>

                {{-- PAGE-CONTENT --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="hero-card">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-monitor"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Topbar Information</h5>
                                    <small class="text-muted">Enter the business name, slogan, and contact details displayed in the top bar.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form method="post" action="{{ route('cms.topbar.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="row mb-4">
                                        {{-- Business Name --}}
                                        <div class="col-md-6 mb-4">
                                            <label for="business_name" class="pro-label">Business Name</label>
                                            <input type="text" class="pro-input @error('business_name') is-invalid @enderror"
                                                name="business_name" placeholder="Enter business name" id="business_name"
                                                value="{{ $data->business_name ?? old('business_name') }}">
                                            @error('business_name')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Slogan --}}
                                        <div class="col-md-6 mb-4">
                                            <label for="slogan" class="pro-label">Slogan</label>
                                            <input type="text" class="pro-input @error('slogan') is-invalid @enderror"
                                                name="slogan" placeholder="Enter slogan" id="slogan"
                                                value="{{ $data->slogan ?? old('slogan') }}">
                                            @error('slogan')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Email --}}
                                        <div class="col-md-6 mb-4">
                                            <label for="email" class="pro-label">Email Address</label>
                                            <input type="email" class="pro-input @error('email') is-invalid @enderror"
                                                name="email" placeholder="Enter email" id="email"
                                                value="{{ $data->email ?? old('email') }}">
                                            @error('email')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Phone --}}
                                        <div class="col-md-6 mb-4">
                                            <label for="phone" class="pro-label">Phone Number</label>
                                            <input type="text" class="pro-input @error('phone') is-invalid @enderror"
                                                name="phone" placeholder="Enter phone number" id="phone"
                                                value="{{ $data->phone ?? old('phone') }}">
                                            @error('phone')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        
                                        {{-- Address --}}
                                        <div class="col-12 mb-2">
                                            <label for="address" class="pro-label">Physical Address</label>
                                            <input type="text" class="pro-input @error('address') is-invalid @enderror"
                                                name="address" placeholder="Enter physical address" id="address"
                                                value="{{ $data->address ?? old('address') }}">
                                            @error('address')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <div class="text-end pt-3 border-top" style="border-color: var(--pro-border) !important;">
                                        <button class="pro-btn pro-btn-primary px-5" type="submit">
                                            <i class="fe fe-save me-2"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
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
    </style>
@endpush
