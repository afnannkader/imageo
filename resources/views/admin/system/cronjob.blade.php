@extends('admin.layouts.application')
@section('title', admin_lang('Cron Job'))
@section('container', 'container-max-lg')
@section('content')
    <div class="card">
        <div class="card-header">
            <span>{{ admin_lang('Command') }}</span>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                @if (@$settings->cronjob->last_execution)
                    <div class="mb-2">
                        <i class="fw-light">
                            {{ str('Last Execution: {datetime}')->replace('{datetime}', dateFormat(@$settings->cronjob->last_execution)) }}
                        </i>
                    </div>
                @endif
                <div class="input-group">
                    <input id="cronInput" type="text" class="form-control form-control-lg"
                        value="wget -q -O /dev/null {{ @$settings->cronjob->key ? route('cronjob', ['key' => @$settings->cronjob->key]) : route('cronjob') }}"
                        readonly>
                    <button class="btn btn-primary btn-lg copy-btn" type="button" data-clipboard-target="#cronInput"><i
                            class="far fa-clone"></i></button>
                </div>
                <div class="input-text mt-2">
                    {{ admin_lang('The cron job command must be set to run every minute') }} ( <code>* * * * *</code> ).
                </div>
            </div>
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-auto">
                    <form action="{{ route('admin.system.cronjob.key-generate') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-primary btn-lg w-100 action-confirm">
                            <i class="fa-solid fa-rotate me-2"></i>
                            {{ admin_lang('Generate Key') }}</button>
                    </form>
                </div>
                <div class="col-12 col-lg-auto">
                    <form action="{{ route('admin.system.cronjob.key-remove') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger btn-lg w-100 action-confirm" @disabled(!$settings->cronjob->key)>
                            <i class="fa-regular fa-trash-can me-2"></i>
                            {{ admin_lang('Remove Key') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/clipboard/clipboard.min.js') }}"></script>
    @endpush
@endsection
