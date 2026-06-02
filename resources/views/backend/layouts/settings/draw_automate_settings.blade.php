@extends('backend.app')
@section('title', 'Draw Automation Settings')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">
                            Draw Automation Settings
                        </h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Draw Automation</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-header d-flex align-items-center gap-2 border-bottom py-3">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle me-2">
                                    <i class="fa-solid fa-gears text-primary fs-5"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0 fw-semibold">Automation Parameters</h5>
                                    <small class="text-muted">Configure logic used when automatically processing weekly
                                        draws.</small>
                                </div>
                            </div>

                            <div class="card-body pt-4">
                                <form class="form form-horizontal" method="post"
                                    action="{{ route('setting.draw-automate.update') }}">
                                    @csrf

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="admin_fee_percentage" class="form-label fw-semibold">
                                                Admin Fee Percentage (%)
                                            </label>
                                            <div class="input-group">
                                                <input
                                                    class="form-control @error('admin_fee_percentage') is-invalid @enderror"
                                                    id="admin_fee_percentage" name="admin_fee_percentage" type="number"
                                                    step="0.01" min="0" max="100"
                                                    value="{{ $settings->admin_fee_percentage ?? old('admin_fee_percentage') }}">
                                                <span class="input-group-text">%</span>
                                                @error('admin_fee_percentage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Percentage of the total pool taken as admin commission
                                                (e.g., 7.50).</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="odds_ratio" class="form-label fw-semibold">
                                                Odds Ratio (1 per X participants)
                                            </label>
                                            <div class="input-group">
                                                <input class="form-control @error('odds_ratio') is-invalid @enderror"
                                                    id="odds_ratio" name="odds_ratio" type="number" min="1"
                                                    value="{{ $settings->odds_ratio ?? old('odds_ratio') }}">
                                                @error('odds_ratio')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Number of participants per 1 winner (e.g.,
                                                400).</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="minimum_participants" class="form-label fw-semibold">
                                                Minimum Participants
                                            </label>
                                            <div class="input-group">
                                                <input
                                                    class="form-control @error('minimum_participants') is-invalid @enderror"
                                                    id="minimum_participants" name="minimum_participants" type="number"
                                                    min="1"
                                                    value="{{ $settings->minimum_participants ?? old('minimum_participants') }}">
                                                @error('minimum_participants')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Minimum total participants required to run the
                                                draw.</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="winner_exclusion_months" class="form-label fw-semibold">
                                                Winner Exclusion Period (Months)
                                            </label>
                                            <div class="input-group">
                                                <input
                                                    class="form-control @error('winner_exclusion_months') is-invalid @enderror"
                                                    id="winner_exclusion_months" name="winner_exclusion_months"
                                                    type="number" min="0"
                                                    value="{{ $settings->winner_exclusion_months ?? old('winner_exclusion_months') }}">
                                                <span class="input-group-text">Months</span>
                                                @error('winner_exclusion_months')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">Months a user is excluded from winning again after
                                                they win (e.g., 6).</small>
                                        </div>
                                    </div>

                                    <hr class="mt-4 mb-4">
                                    <h5 class="fw-semibold mb-3">Schedule Configuration</h5>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="draw_start_day" class="form-label fw-semibold">Draw Start
                                                Day</label>
                                            <select class="form-select @error('draw_start_day') is-invalid @enderror"
                                                id="draw_start_day" name="draw_start_day">
                                                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                    <option value="{{ $day }}"
                                                        {{ ($settings->draw_start_day ?? old('draw_start_day')) === $day ? 'selected' : '' }}>
                                                        {{ $day }}</option>
                                                @endforeach
                                            </select>
                                            @error('draw_start_day')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Day of the week the new draw automatically
                                                starts.</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="draw_start_time" class="form-label fw-semibold">Draw Start
                                                Time</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
                                                <input type="text"
                                                    class="form-control time-picker @error('draw_start_time') is-invalid @enderror"
                                                    id="draw_start_time" name="draw_start_time"
                                                    placeholder="Select Start Time"
                                                    value="{{ \Carbon\Carbon::parse($settings->draw_start_time ?? '00:00:00')->format('H:i') }}">
                                            </div>
                                            @error('draw_start_time')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">Time of day the new draw starts.</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="draw_end_day" class="form-label fw-semibold">Draw Finalize
                                                Day</label>
                                            <select class="form-select @error('draw_end_day') is-invalid @enderror"
                                                id="draw_end_day" name="draw_end_day">
                                                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                    <option value="{{ $day }}"
                                                        {{ ($settings->draw_end_day ?? old('draw_end_day')) === $day ? 'selected' : '' }}>
                                                        {{ $day }}</option>
                                                @endforeach
                                            </select>
                                            @error('draw_end_day')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Day of the week the draw finalizes and selects
                                                winners.</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="draw_end_time" class="form-label fw-semibold">Draw Finalize
                                                Time</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
                                                <input type="text"
                                                    class="form-control time-picker @error('draw_end_time') is-invalid @enderror"
                                                    id="draw_end_time" name="draw_end_time"
                                                    placeholder="Select Finalize Time"
                                                    value="{{ \Carbon\Carbon::parse($settings->draw_end_time ?? '17:00:00')->format('H:i') }}">
                                            </div>
                                            @error('draw_end_time')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">Time of day the draw finalizes.</small>
                                        </div>
                                    </div>

                                    <hr class="mt-4 mb-3">

                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-success px-4" type="submit">
                                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Settings
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

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
    <style>
        .flatpickr-input[readonly] {
            cursor: pointer;
            background-color: #fff !important;
        }
        .input-group .flatpickr-input {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr(".time-picker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "h:i K", // Displays in AM/PM 12-hour format
            });
        });
    </script>
@endpush
