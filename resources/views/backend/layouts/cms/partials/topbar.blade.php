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
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <form class="form-horizontal" method="post"
                                    action="{{ route('cms.topbar.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        <div class="row mb-3">
                                            {{-- Business Name --}}
                                            <div class="form-group mb-3 col-md-6">
                                                <label for="business_name" class="form-label">Business Name</label>
                                                <input type="text"
                                                    class="form-control @error('business_name') is-invalid @enderror"
                                                    name="business_name" placeholder="Enter business name"
                                                    id="business_name"
                                                    value="{{ $data->business_name ?? old('business_name') }}">
                                                @error('business_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Slogan --}}
                                            <div class="form-group mb-3 col-md-6">
                                                <label for="slogan" class="form-label">Slogan</label>
                                                <input type="text"
                                                    class="form-control @error('slogan') is-invalid @enderror"
                                                    name="slogan" placeholder="Enter slogan" id="slogan"
                                                    value="{{ $data->slogan ?? old('slogan') }}">
                                                @error('slogan')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Email --}}
                                            <div class="form-group mb-3 col-md-6">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                                    placeholder="Enter email" id="email"
                                                    value="{{ $data->email ?? old('email') }}">
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Phone --}}
                                            <div class="form-group mb-3 col-md-6">
                                                <label for="phone" class="form-label">Phone</label>
                                                <input type="text"
                                                    class="form-control @error('phone') is-invalid @enderror" name="phone"
                                                    placeholder="Enter phone" id="phone"
                                                    value="{{ $data->phone ?? old('phone') }}">
                                                @error('phone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Address --}}
                                            <div class="form-group mb-3 col-md-12">
                                                <label for="address" class="form-label">Address</label>
                                                <input type="text"
                                                    class="form-control @error('address') is-invalid @enderror"
                                                    name="address" placeholder="Enter address" id="address"
                                                    value="{{ $data->address ?? old('address') }}">
                                                @error('address')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
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
                </div>
            </div>
        </div>
    </div>

@endsection
