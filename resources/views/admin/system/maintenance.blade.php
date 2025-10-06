@extends('admin.layouts.form')
@section('title', admin_lang('Maintenance Mode'))
@section('container', 'container-max-lg')
@section('content')
    <form id="vironeer-submited-form" action="{{ route('admin.system.maintenance') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="note note-warning">
            <strong>{{ admin_lang('Note!') }}</strong>
            <span>{{ admin_lang('As an admin, you can still view and control your website but the visitors will redirect to the maintenance page.') }}</span>
        </div>
        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="col-lg-4">
                            <label class="form-label">{{ admin_lang('Status') }}</label>
                            <input type="checkbox" name="maintenance[status]" data-toggle="toggle" data-height="40px"
                                {{ $settings->maintenance->status ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ admin_lang('Title') }}</label>
                        <input name="maintenance[title]" class="form-control form-control-md"
                            value="{{ $settings->maintenance->title }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ admin_lang('Body') }}</label>
                        <textarea name="maintenance[body]" class="form-control ckeditor" rows="8">{{ $settings->maintenance->body }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/ckeditor/ckeditor.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/ckeditor/plugins/uploadAdapterPlugin.js') }}"></script>
    @endpush
@endsection
