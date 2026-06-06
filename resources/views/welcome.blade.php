@extends('auth.app')

@section('title', 'Welcome')

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
            <div class="text-center" style="margin-bottom: 1.5rem;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fe fe-user" style="color: #fff; font-size: 1.5rem;"></i>
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.25rem;">Welcome Back</h2>
                <p style="color: #6c757d; font-size: 0.875rem; margin-bottom: 0;">Sign in to access your admin dashboard</p>
            </div>

            <div style="margin-top: 1.75rem;">
                <a href="{{ route('login') }}" class="btn btn-primary" style="width: 100%; padding: 0.625rem; font-size: 0.875rem; font-weight: 600; border-radius: 0.375rem; letter-spacing: 0.025em;">
                    Sign In
                </a>
            </div>
        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection



