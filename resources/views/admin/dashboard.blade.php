@extends('admin.layouts.application')
@section('title', admin_lang('Dashboard'))
@section('access', admin_lang('Quick Access'))
@section('container', 'container-max-xxl')
@section('content')
    @if (!$settings->cronjob->last_execution)
        <div class="alert alert-danger p-4 mb-4">
            <div class="row g-4">
                <div class="col-auto">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                </div>
                <div class="col">
                    <h4>{{ admin_lang('Cron Job Not Working') }}</h4>
                    <p class="mb-2">
                        {{ admin_lang("It seems that your Cron Job isn't set up correctly, which might be causing it not to work as expected. Please double-check and ensure that your Cron Job is properly configured.") }}
                    </p>
                    <hr>
                    <p class="mb-3">
                        {{ admin_lang('Cron Job is required by multiple things to be run (Subscriptions, Transactions, Expired Images, Sitemap, etc...)') }}
                    </p>
                    <a href="{{ route('admin.system.cronjob.index') }}"
                        class="btn btn-outline-danger">{{ admin_lang('Setup Cron Job') }}<i
                            class="fa-solid fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    @endif
    @if (!$settings->smtp->status)
        <div class="alert alert-warning border border-warning p-4 mb-4">
            <div class="row row-cols-auto g-4">
                <div class="col">
                    <i class="fa-solid fa-circle-info fa-3x"></i>
                </div>
                <div class="col">
                    <h4>{{ admin_lang('SMTP Is Not Enabled') }}</h4>
                    <p class="mb-3">
                        {{ admin_lang('SMTP is not enabled, set it now to be able to recover the password and use all the features that needs to send an email.') }}
                    </p>
                    <a href="{{ route('admin.settings.smtp.index') }}"
                        class="btn btn-outline-dark">{{ admin_lang('Setup SMTP') }}<i
                            class="fa-solid fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    @endif
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-2 row-cols-xxl-3 g-3 mb-3">
        <div class="col">
            <div class="vironeer-counter-card bg-c2">
                <div class="vironeer-counter-card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="vironeer-counter-card-meta">
                    <p class="vironeer-counter-card-title">{{ admin_lang('Total Earnings') }}</p>
                    <p class="vironeer-counter-card-number">{{ priceSymbol($widget['total_earnings']) }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="vironeer-counter-card bg-c1">
                <div class="vironeer-counter-card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="vironeer-counter-card-meta">
                    <p class="vironeer-counter-card-title">{{ admin_lang('Total Users') }}</p>
                    <p class="vironeer-counter-card-number">{{ number_format($widget['total_users']) }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="vironeer-counter-card bg-c-1">
                <div class="vironeer-counter-card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="vironeer-counter-card-meta">
                    <p class="vironeer-counter-card-title">{{ admin_lang('Total Images') }}</p>
                    <p class="vironeer-counter-card-number">{{ number_format($widget['total_generated_images']) }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="vironeer-counter-card bg-c-2">
                <div class="vironeer-counter-card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="vironeer-counter-card-meta">
                    <p class="vironeer-counter-card-title">{{ admin_lang('Current Month Earnings') }}</p>
                    <p class="vironeer-counter-card-number">{{ priceSymbol($widget['current_month_earnings']) }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="vironeer-counter-card bg-c-5">
                <div class="vironeer-counter-card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="vironeer-counter-card-meta">
                    <p class="vironeer-counter-card-title">{{ admin_lang('Current Month Users') }}</p>
                    <p class="vironeer-counter-card-number">{{ number_format($widget['current_month_users']) }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="vironeer-counter-card bg-c-7">
                <div class="vironeer-counter-card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="vironeer-counter-card-meta">
                    <p class="vironeer-counter-card-title">{{ admin_lang('Current Month Images') }}</p>
                    <p class="vironeer-counter-card-number">{{ number_format($widget['current_month_generated_images']) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4 col-xxl-4">
            <div class="card vhp-460">
                <div class="vironeer-box v2">
                    <div class="vironeer-box-header mb-3">
                        <p class="vironeer-box-header-title large mb-0">{{ admin_lang('Recently transactions') }}</p>
                        <div class="vironeer-box-header-action ms-auto">
                            <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-sm-end">
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.transactions.index') }}">{{ admin_lang('View All') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="vironeer-box-body">
                        <div class="vironeer-random-lists">
                            @forelse ($transactions as $transaction)
                                <div class="vironeer-random-list">
                                    <div class="vironeer-random-list-cont">
                                        <div class="vironeer-random-list-info">
                                            <div>
                                                <a class="vironeer-random-list-title fs-exact-14"
                                                    href="{{ route('admin.transactions.edit', $transaction->id) }}">
                                                    #{{ $transaction->id }}
                                                </a>
                                                <p class="vironeer-random-list-text mb-0">
                                                    {{ $transaction->created_at->diffforhumans() }}
                                                </p>
                                            </div>
                                            <div class="vironeer-random-list-action">
                                                <span class="text-success">+
                                                    <strong>{{ priceSymbol($transaction->total) }}</strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                @include('admin.includes.emptysmall')
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8 col-xxl-8">
            <div class="card">
                <div class="vironeer-box chart-bar">
                    <div class="vironeer-box-header">
                        <p class="vironeer-box-header-title large mb-0">
                            {{ admin_lang('Earnings Statistics For This Week') }}
                        </p>
                        <div class="vironeer-box-header-action ms-auto">
                            <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-sm-end">
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.transactions.index') }}">{{ admin_lang('View Transactions') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="vironeer-box-body">
                        <div class="chart-bar">
                            <canvas height="380" id="vironeer-earnings-charts"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8 col-xxl-8">
            <div class="card">
                <div class="vironeer-box chart-bar">
                    <div class="vironeer-box-header">
                        <p class="vironeer-box-header-title large mb-0">{{ admin_lang('Users Statistics For This Week') }}
                        </p>
                        <div class="vironeer-box-header-action ms-auto">
                            <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-sm-end">
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.users.index') }}">{{ admin_lang('View All') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="vironeer-box-body">
                        <div class="chart-bar">
                            <canvas height="380" id="vironeer-users-charts"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-xxl-4">
            <div class="card vhp-460">
                <div class="vironeer-box v2">
                    <div class="vironeer-box-header mb-3">
                        <p class="vironeer-box-header-title large mb-0">{{ admin_lang('Recently registered') }}</p>
                        <div class="vironeer-box-header-action ms-auto">
                            <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-sm-end">
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.users.index') }}">{{ admin_lang('View All') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="vironeer-box-body">
                        <div class="vironeer-random-lists">
                            @forelse ($users as $user)
                                <div class="vironeer-random-list">
                                    <div class="vironeer-random-list-cont">
                                        <a class="vironeer-random-list-img" href="#">
                                            <img src="{{ asset($user->avatar) }}" />
                                        </a>
                                        <div class="vironeer-random-list-info">
                                            <div>
                                                <a class="vironeer-random-list-title fs-exact-14"
                                                    href="{{ route('admin.users.edit', $user->id) }}">
                                                    {{ $user->name }}
                                                </a>
                                                <p class="vironeer-random-list-text mb-0">
                                                    {{ $user->created_at->diffforhumans() }}
                                                </p>
                                            </div>
                                            <div class="vironeer-random-list-action d-none d-lg-block">
                                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                                    class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                @include('admin.includes.emptysmall')
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="vironeer-box chart-bar">
                    <div class="vironeer-box-header">
                        <p class="vironeer-box-header-title large mb-0">{{ admin_lang('Login Statistics - Browsers') }}
                        </p>
                        <small class="text-muted ms-auto">({{ carbon()->now()->format('F') }})</small>
                    </div>
                    @if ($countUsersLogs)
                        <div class="vironeer-box-body">
                            <div class="chart-bar">
                                <canvas id="vironeer-browsers-charts"></canvas>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            @include('admin.includes.emptysmall')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="vironeer-box chart-bar">
                    <div class="vironeer-box-header">
                        <p class="vironeer-box-header-title large mb-0">
                            {{ admin_lang('Login Statistics - Operating Systems') }}
                        </p>
                        <small class="text-muted ms-auto">({{ carbon()->now()->format('F') }})</small>
                    </div>
                    @if ($countUsersLogs)
                        <div class="vironeer-box-body">
                            <div class="chart-bar">
                                <canvas id="vironeer-os-charts"></canvas>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            @include('admin.includes.emptysmall')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="vironeer-box chart-bar">
                    <div class="vironeer-box-header">
                        <p class="vironeer-box-header-title large mb-0">{{ admin_lang('Login Statistics - Countries') }}
                        </p>
                        <small class="text-muted ms-auto">({{ carbon()->now()->format('F') }})</small>
                    </div>
                    @if ($countUsersLogs)
                        <div class="vironeer-box-body">
                            <div class="chart-bar">
                                <canvas id="vironeer-countries-charts"></canvas>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            @include('admin.includes.emptysmall')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @push('top_scripts')
        <script type="text/javascript">
            "use strict";
            const CURRENCY_CODE = "{{ $settings->currency->symbol }}";
            const CURRENCY_POSITION = "{{ $settings->currency->position }}";
        </script>
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/chartjs/chart.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/admin/js/charts.js') }}"></script>
    @endpush
@endsection
