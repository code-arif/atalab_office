@extends('backend.app')

@section('title', 'Quote section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Home page - Quote section</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Quote section</li>
                        </ol>
                    </div>
                </div>

                {{-- PAGE-CONTENT --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card box-shadow-0">
                            <div class="card-header bg-light">
                                <h4 class="card-title">Quote</h4>
                            </div>
                            <div class="card-body">
                                <form class="form-horizontal" method="post"
                                    action="{{ route('cms.home.quote.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        {{-- Title --}}
                                        <div class="form-group mb-3">
                                            <label for="title" class="form-label">Quote</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter title" id="title"
                                                value="{{ $data->title ?? old('title') }}">
                                            @error('title')
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
