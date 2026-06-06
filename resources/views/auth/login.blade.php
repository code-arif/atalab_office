@extends('auth.app')

@section('title', 'Admin Login')

@section('content')
<!-- CONTAINER OPEN -->
<div class="col col-login mx-auto text-center">
    <a href="{{ url('/') }}" class="text-center d-inline-block mb-3">
        <img src="{{ asset($settings->favicon ?? 'default/logo.png') }}" class="header-brand-img" alt="Logo">
    </a>
</div>
<div class="container-login100">
    <div class="wrap-login100 p-0">
        <div class="card-body" style="padding: 2.5rem;">
            <form class="login100-form validate-form" method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="login100-form-title">
                    <h2 style="font-size: 1.5rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.25rem;">Welcome Back</h2>
                    <p style="color: #6c757d; font-size: 0.875rem; margin-bottom: 0;">Sign in to your admin account</p>
                </div>

                <div style="margin-top: 1.75rem;">
                    <label for="email" style="font-size: 0.8125rem; font-weight: 600; color: #495057; display: block; text-align: left; margin-bottom: 0.375rem;">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: #f8f9fa; border-right: none; border-radius: 0.375rem 0 0 0.375rem; color: #6c757d;">
                            <i class="fe fe-mail"></i>
                        </span>
                        <input class="form-control" type="email" name="email" id="email" placeholder="admin@example.com" required
                            style="border-left: none; border-radius: 0 0.375rem 0.375rem 0; padding: 0.625rem 0.75rem; font-size: 0.875rem;">
                    </div>
                    @error('email')
                    <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-top: 1rem;">
                    <label for="password" style="font-size: 0.8125rem; font-weight: 600; color: #495057; display: block; text-align: left; margin-bottom: 0.375rem;">Password</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: #f8f9fa; border-right: none; border-radius: 0.375rem 0 0 0.375rem; color: #6c757d;">
                            <i class="fe fe-lock"></i>
                        </span>
                        <input class="form-control" type="password" name="password" id="password" placeholder="Enter your password" required
                            style="border-left: none; border-radius: 0 0.375rem 0.375rem 0; padding: 0.625rem 0.75rem; font-size: 0.875rem;">
                    </div>
                    @error('password')
                    <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-block" style="width: 100%; padding: 0.625rem; font-size: 0.875rem; font-weight: 600; border-radius: 0.375rem; letter-spacing: 0.025em;">
                        Sign In
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
