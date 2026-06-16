@extends('backend.app')
@section('title', 'Stripe Settings')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">
                            Stripe Settings
                            <i class="fa-solid fa-triangle-exclamation text-danger ms-1"
                                title="Sensitive — handle with care"></i>
                        </h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Stripe</li>
                        </ol>
                    </div>
                </div>
                {{-- END PAGE-HEADER --}}

                {{-- ============================================================ --}}
                {{-- CARD 1 — Stripe API Credentials                              --}}
                {{-- ============================================================ --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card box-shadow-0">

                            <div class="card-header d-flex align-items-center gap-2 border-bottom py-3">
                                <span
                                    class="d-flex align-items-center justify-content-center bg-primary-transparent rounded-circle me-2"
                                    style="width:48px;height:48px;">
                                    <i class="fa-brands fa-stripe text-primary fs-5"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0 fw-semibold">API Credentials</h5>
                                    <small class="text-muted">Your Stripe publishable key, secret key, and webhook
                                        secrets.</small>
                                </div>
                            </div>

                            <div class="card-body pt-4">
                                <form class="form form-horizontal" method="post"
                                    action="{{ route('setting.stripe.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')

                                    {{-- Stripe Key --}}
                                    <div class="row mb-4 align-items-start">
                                        <label for="stripe_key" class="col-md-3 form-label fw-semibold pt-2">
                                            Stripe Key
                                            <span
                                                class="badge bg-primary-transparent text-primary ms-1 fs-10">Publishable</span>
                                        </label>
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                                <input class="form-control @error('stripe_key') is-invalid @enderror"
                                                    id="stripe_key" name="stripe_key"
                                                    placeholder="pk_live_xxxxxxxxxxxxxxxxxxxxxxxx" type="text"
                                                    value="{{ env('STRIPE_KEY') ?? old('stripe_key') }}">
                                            </div>
                                            @error('stripe_key')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Your Stripe publishable key (starts with
                                                <code>pk_</code>).</small>
                                        </div>
                                    </div>

                                    {{-- Stripe Secret --}}
                                    <div class="row mb-4 align-items-start">
                                        <label for="stripe_secret" class="col-md-3 form-label fw-semibold pt-2">
                                            Stripe Secret
                                            <span class="badge bg-danger-transparent text-danger ms-1 fs-10">Private</span>
                                        </label>
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                                <input class="form-control @error('stripe_secret') is-invalid @enderror"
                                                    id="stripe_secret" name="stripe_secret"
                                                    placeholder="sk_live_xxxxxxxxxxxxxxxxxxxxxxxx" type="password"
                                                    value="{{ env('STRIPE_SECRET') ?? old('stripe_secret') }}">
                                            </div>
                                            @error('stripe_secret')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Your Stripe secret key (starts with <code>sk_</code>).
                                                Keep this confidential.</small>
                                        </div>
                                    </div>

                                    {{-- Webhook Secret (V1) --}}
                                    <div class="row mb-4 align-items-start">
                                        <label for="stripe_webhook_secret" class="col-md-3 form-label fw-semibold pt-2">
                                            Webhook Secret (V1)
                                            <span
                                                class="badge bg-warning-transparent text-warning ms-1 fs-10">Webhook</span>
                                        </label>
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
                                                <input
                                                    class="form-control @error('stripe_webhook_secret') is-invalid @enderror"
                                                    id="stripe_webhook_secret" name="stripe_webhook_secret"
                                                    placeholder="whsec_xxxxxxxxxxxxxxxxxxxxxxxx" type="text"
                                                    value="{{ env('STRIPE_WEBHOOK_SECRET') ?? old('stripe_webhook_secret') }}">
                                            </div>
                                            @error('stripe_webhook_secret')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">For the V1 endpoint: <code>/webhook/stripe</code>. Found in Stripe Dashboard under
                                                <strong>Developers → Webhooks</strong>.</small>
                                        </div>
                                    </div>

                                    {{-- Webhook Secret (V2) --}}
                                    <div class="row mb-4 align-items-start">
                                        <label for="stripe_v2_webhook_secret" class="col-md-3 form-label fw-semibold pt-2">
                                            Webhook Secret (V2)
                                            <span
                                                class="badge bg-warning-transparent text-warning ms-1 fs-10">Webhook</span>
                                        </label>
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
                                                <input
                                                    class="form-control @error('stripe_v2_webhook_secret') is-invalid @enderror"
                                                    id="stripe_v2_webhook_secret" name="stripe_v2_webhook_secret"
                                                    placeholder="whsec_xxxxxxxxxxxxxxxxxxxxxxxx" type="text"
                                                    value="{{ env('STRIPE_V2_WEBHOOK_SECRET') ?? old('stripe_v2_webhook_secret') }}">
                                            </div>
                                            @error('stripe_v2_webhook_secret')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">For the V2 endpoint: <code>/v2/webhook/stripe</code>. Found in Stripe Dashboard under
                                                <strong>Developers → Webhooks</strong>.</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-9 offset-md-3">
                                            <button class="btn btn-primary px-4" type="submit">
                                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Credentials
                                            </button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- CARD 2 — Stripe Fees, Processing Fees & Donation Amount      --}}
                {{-- ============================================================ --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">

                            <div class="card-header d-flex align-items-center gap-2 border-bottom py-3">
                                <span
                                    class="d-flex align-items-center justify-content-center bg-success-transparent rounded-circle me-2"
                                    style="width:50px;height:50px;">
                                    <i class="fa-solid fa-percent text-success fs-5"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0 fw-semibold">Fees & Donation Configuration</h5>
                                    <small class="text-muted">Configure platform fees, Stripe processing charges, and
                                        default donation amounts.</small>
                                </div>
                            </div>

                            <div class="card-body pt-4">
                                <form class="form form-horizontal" method="post"
                                    action="{{ route('stripe.update-percentage') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')

                                    {{-- Section label --}}
                                    <p class="text-muted fs-12 mb-4">
                                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                        All fee values are applied during payment processing. Ensure accuracy before saving.
                                    </p>

                                    <div class="row g-4">

                                        {{-- Software / Platform Fee --}}
                                        {{-- <div class="col-md-6">
                                            <label for="admin_percentage" class="form-label fw-semibold">
                                                Software Fee (%)
                                            </label>
                                            <div class="input-group">
                                                <input class="form-control @error('admin_percentage') is-invalid @enderror"
                                                    id="admin_percentage" name="admin_percentage"
                                                    placeholder="e.g. 2.5"
                                                    type="number" step="0.01" min="0"
                                                    value="{{ $settings->admin_percentage ?? old('admin_percentage') }}">
                                                <span class="input-group-text">%</span>
                                                @error('admin_percentage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Platform / software service fee charged on top of the donation.</small>
                                        </div> --}}

                                        {{-- ACH Flat Fee --}}
                                        <div class="col-md-6">
                                            <label for="ach_flat_fee" class="form-label fw-semibold">
                                                ACH Flat Fee ($)
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input class="form-control @error('ach_flat_fee') is-invalid @enderror"
                                                    id="ach_flat_fee" name="ach_flat_fee" placeholder="e.g. 0.25"
                                                    type="number" step="0.01" min="0"
                                                    value="{{ $settings->ach_flat_fee ?? old('ach_flat_fee') }}">
                                                @error('ach_flat_fee')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Fixed flat fee applied to ACH (bank transfer)
                                                payments.</small>
                                        </div>

                                        {{-- Card Fee Percentage --}}
                                        <div class="col-md-6">
                                            <label for="card_fee_percentage" class="form-label fw-semibold">
                                                Card Fee Percentage (%)
                                            </label>
                                            <div class="input-group">
                                                <input
                                                    class="form-control @error('card_fee_percentage') is-invalid @enderror"
                                                    id="card_fee_percentage" name="card_fee_percentage"
                                                    placeholder="e.g. 2.9" type="number" step="0.01" min="0"
                                                    value="{{ $settings->card_fee_percentage ?? old('card_fee_percentage') }}">
                                                <span class="input-group-text">%</span>
                                                @error('card_fee_percentage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Percentage fee charged for card payments (e.g.
                                                Stripe's 2.9%).</small>
                                        </div>

                                        {{-- Card Fixed Fee --}}
                                        <div class="col-md-6">
                                            <label for="card_fixed_fee" class="form-label fw-semibold">
                                                Card Fixed Fee ($)
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input class="form-control @error('card_fixed_fee') is-invalid @enderror"
                                                    id="card_fixed_fee" name="card_fixed_fee" placeholder="e.g. 0.30"
                                                    type="number" step="0.01" min="0"
                                                    value="{{ $settings->card_fixed_fee ?? old('card_fixed_fee') }}">
                                                @error('card_fixed_fee')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Fixed amount added per card transaction (e.g.
                                                Stripe's $0.30).</small>
                                        </div>

                                        {{-- Donation Amount --}}
                                        <div class="col-md-6">
                                            <label for="donation_amount" class="form-label fw-semibold">
                                                Donation Amount ($)
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input class="form-control @error('donation_amount') is-invalid @enderror"
                                                    id="donation_amount" name="donation_amount" placeholder="e.g. 10.00"
                                                    type="number" step="0.01" min="0"
                                                    value="{{ $settings->donation_amount ?? old('donation_amount') }}">
                                                @error('donation_amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Default or fixed donation amount per
                                                transaction.</small>
                                        </div>

                                    </div>{{-- end .row.g-4 --}}

                                    <hr class="mt-4 mb-3">

                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-success px-4" type="submit">
                                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Fee Settings
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
@endpush
