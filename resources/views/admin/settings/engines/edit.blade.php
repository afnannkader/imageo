@extends('admin.layouts.form')
@section('section', admin_lang('Settings'))
@section('title', admin_lang('Edit | ') . $engine->name)
@section('back', route('admin.settings.engines.index'))
@section('content')
    <form id="vironeer-submited-form" action="{{ route('admin.settings.engines.update', $engine->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-7">
                <div class="card custom-card mb-4">
                    <div class="card-body p-4">
                        <div class="vironeer-file-preview-box bg-light mb-3 p-4 text-center">
                            <div class="file-preview-box mb-3">
                                <img id="filePreview" src="{{ asset($engine->logo) }}" height="100">
                            </div>
                        </div>
                        <div class="row g-3 mb-2">
                            <div class="col-lg-6">
                                <label class="form-label">{{ admin_lang('Name') }} : </label>
                                <input class="form-control" name="name" value="{{ $engine->name }}" required>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">{{ admin_lang('Status') }} :</label>
                                <input type="checkbox" name="status" data-toggle="toggle"
                                    {{ $engine->isActive() ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <i class="fa-solid fa-key me-2"></i>{{ admin_lang('Credentials') }}
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-2">
                            @foreach ($engine->credentials as $key => $value)
                                <div class="col-lg-12">
                                    <label class="form-label capitalize">{{ str_replace('_', ' ', $key) }} :</label>
                                    <input type="text" name="credentials[{{ $key }}]"
                                        value="{{ demoMode() ? '' : $value }}" class="form-control remove-spaces">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if ($engine->instructions)
                    <div class="card custom-card">
                        <div class="card-header">
                            <i class="far fa-question-circle me-2"></i>{{ admin_lang('Instructions') }}
                        </div>
                        <div class="card-body">
                            {!! str_replace('[URL]', url('/'), $engine->instructions) !!}
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-lg-5">
                <div class="card custom-card mb-4">
                    <div class="card-header">{{ admin_lang('Filters') }}</div>
                    <div class="card-body p-4">
                        <p>{{ admin_lang('Enter the words that you do not want to allow to be generated (bad words, sexual words, etc...)') }}
                        </p>
                        <input type="text" name="filters" class="form-control tags-input"
                            placeholder="{{ admin_lang('Enter the words') }}" value="{{ $engine->filters }}">
                    </div>
                </div>
                <div class="card custom-card mb-4">
                    <div class="card-header">{{ admin_lang('Sizes') }}</div>
                    <div class="card-body p-4">
                        <input type="text" name="sizes" class="form-control sizes-input"
                            placeholder="{{ admin_lang('Enter the sizes') }}" value="{{ $engine->sizes }}">
                    </div>
                </div>
                <div class="card custom-card mb-4">
                    <div class="card-header">{{ admin_lang('Art Styles') }}</div>
                    <div class="card-body p-4">
                        <input type="text" name="art_styles" class="form-control tags-input"
                            placeholder="{{ admin_lang('Enter the art styles') }}" value="{{ $engine->art_styles }}">
                    </div>
                </div>
                <div class="card custom-card mb-4">
                    <div class="card-header">{{ admin_lang('Lightning Styles') }}</div>
                    <div class="card-body p-4">
                        <input type="text" name="lightning_styles" class="form-control tags-input"
                            placeholder="{{ admin_lang('Enter the lightning styles') }}"
                            value="{{ $engine->lightning_styles }}">
                    </div>
                </div>
                <div class="card custom-card mb-4">
                    <div class="card-header">{{ admin_lang('Moods') }}</div>
                    <div class="card-body p-4">
                        <input type="text" name="moods" class="form-control tags-input"
                            placeholder="{{ admin_lang('Enter the moods') }}" value="{{ $engine->moods }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
    @endpush
    @push('scripts')
        <script>
            "use strict";
            $(function() {
                let tagsInput = $('.tags-input');
                tagsInput.tagsinput({
                    cancelConfirmKeysOnEmpty: false
                });
                tagsInput.on('beforeItemAdd', function(event) {
                    if (!/^[a-zA-Z-0-9, ]+$/.test(event.item)) {
                        event.cancel = true;
                        toastr.error('{{ admin_lang('Enter the filters without any symbols') }}');
                    }
                });

                let sizesInput = $('.sizes-input');
                sizesInput.tagsinput({
                    cancelConfirmKeysOnEmpty: false
                });
                sizesInput.on('beforeItemAdd', function(event) {
                    if (!/^\d+([x:]\d+)$/.test(event.item)) {
                        event.cancel = true;
                        toastr.error('{{ admin_lang('The size format is invalid') }}');
                    }
                });
            });
        </script>
    @endpush
@endsection
