@extends('backend.app')

@section('title', 'Contact Us Page')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Contact Us Page</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Contact Us Page</a></li>
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
                                    action="{{ route('cms.contact_us.hero.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        <div class="row mb-3">
                                            {{-- Title --}}
                                            <div class="form-group mb-3 col-md-4">
                                                <label for="title" class="form-label">Title</label>
                                                <input type="text"
                                                    class="form-control @error('title') is-invalid @enderror" name="title"
                                                    placeholder="Enter title" id="title"
                                                    value="{{ $data->title ?? old('title') }}">
                                                @error('title')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Email --}}
                                            <div class="form-group mb-3 col-md-4">
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
                                            <div class="form-group mb-3 col-md-4">
                                                <label for="phone" class="form-label">Phone</label>
                                                <input type="text"
                                                    class="form-control @error('phone') is-invalid @enderror" name="phone"
                                                    placeholder="Enter phone" id="phone"
                                                    value="{{ $data->phone ?? old('phone') }}">
                                                @error('phone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Description --}}
                                        <div class="form-group mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                                rows="4" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
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
