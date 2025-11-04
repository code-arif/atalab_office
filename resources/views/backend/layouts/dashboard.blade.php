@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 -->
                <div class="row">
                    <!-- Total Employees -->
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <a href="#" class="clickable-card">
                            <div class="card overflow-hidden">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h3 class="mb-2 fw-semibold">{{ $totalUsers }}</h3>
                                            <p class="text-muted fs-13 mb-0">Total Donors</p>
                                        </div>
                                        <div class="col col-auto top-icn dash">
                                            <div class="counter-icon bg-success dash ms-auto box-shadow-success">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
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
        .clickable-card {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        .clickable-card .card {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .clickable-card:hover .card {
            background-color: #521aac !important;
            color: #fff !important;
            transform: translateY(-3px);
        }

        .clickable-card:hover p,
        .clickable-card:hover h3,
        .clickable-card:hover svg path {
            color: #fff !important;
            fill: #fff !important;
        }
    </style>
@endpush
