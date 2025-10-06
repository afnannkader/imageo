@extends('layouts.front')
@section('title', $SeoConfiguration->title ?? '')
@section('content')
    {!! ads_home_page_top() !!}
    <header class="header">
        <div class="wrapper">
            <div class="container">
                <div class="wrapper-content">
                    <div class="wrapper-container">
                        <div class="text-center">
                            <h1 class="title mb-3">{{ lang('AI Image Generator', 'home page') }}</h1>
                            <p class="mb-0 text-muted">
                                {{ lang('Create stunning and unique images with ease using our AI image generation.', 'home page') }}
                            </p>
                        </div>
                        @if (subscription())
                            @if (subscription()->is_subscribed)
                                <form id="generator" action="{{ route('images.generator') }}" method="POST">
                                    <div class="card-v mt-5">
                                        <div class="generator-search v2">
                                            @if ($engines && $engines->count() > 0)
                                                <div class="row g-3">
                                                    <div class="col-lg-8">
                                                        <input type="text" name="prompt"
                                                            class="form-control prompt-input"
                                                            placeholder="{{ lang('What do you want to generate?', 'home page') }}"
                                                            value="{{ request('prompt') ?? '' }}" autofocus required />
                                                    </div>
                                                    <div class="col col-lg-2">
                                                        <button type="button" id="generator-options-btn"
                                                            class="btn btn-light px-4 w-100"><i
                                                                class="fa fa-cog me-1"></i>{{ lang('Options', 'home page') }}</button>
                                                    </div>
                                                    <div class="col col-lg-2">
                                                        <button id="generate-button" class="btn btn-primary px-4 w-100"><i
                                                                class="fa-solid fa-rotate me-2"></i>{{ lang('Generate', 'home page') }}</button>
                                                    </div>
                                                </div>
                                                <div class="generator-options d-none">
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <div class="row g-2 g-lg-3 align-items-center">
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="col-form-label">
                                                                        {{ lang('Model/Engine', 'home page') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col">
                                                                    <select id="modelEngine" name="engine"
                                                                        class="form-select form-select-md w-100">
                                                                        @foreach ($engines as $engine)
                                                                            @php
                                                                                $sampleOptions = range(
                                                                                    1,
                                                                                    min(
                                                                                        $engine->max,
                                                                                        subscription()->plan
                                                                                            ->max_images,
                                                                                    ),
                                                                                );
                                                                            @endphp
                                                                            <option value="{{ $engine->alias }}"
                                                                                data-np="{{ $engine->supportNegativePrompt() ? 1 : 0 }}"
                                                                                data-sz="{{ $engine->sizes ? json_encode($engine->getSizesArray()) : '' }}"
                                                                                data-as="{{ $engine->art_styles ? json_encode($engine->getArtStylesArray()) : '' }}"
                                                                                data-ls="{{ $engine->lightning_styles ? json_encode($engine->getLightningStylesArray()) : '' }}"
                                                                                data-moods="{{ $engine->moods ? json_encode($engine->getMoodsArray()) : '' }}"
                                                                                data-samples="{{ json_encode($sampleOptions) }}">
                                                                                {{ $engine->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="negativePrompt" class="col-12">
                                                            <div class="row g-2 g-lg-3 align-items-center">
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="col-form-label">
                                                                        {{ lang('Negative Prompt', 'home page') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col">
                                                                    <input type="text" name="negative_prompt"
                                                                        class="form-control form-control-md"
                                                                        placeholder="{{ lang('What you want to avoid generating?', 'home page') }}" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="sizes" class="col-12">
                                                            <div class="row g-2 g-lg-3 align-items-center">
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="col-form-label">
                                                                        {{ lang('Image Size', 'home page') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col">
                                                                    <select name="size"
                                                                        class="form-select form-select-md w-100" required>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="artStyles" class="col-12">
                                                            <div class="row g-2 g-lg-3 align-items-center">
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="col-form-label">
                                                                        {{ lang('Art Styles', 'home page') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col">
                                                                    <select name="art_style"
                                                                        class="form-select form-select-md w-100">
                                                                        <option value="">--</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="lightningStyles" class="col-12">
                                                            <div class="row g-2 g-lg-3 align-items-center">
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="col-form-label">
                                                                        {{ lang('Lightning Styles', 'home page') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col">
                                                                    <select name="lightning_style"
                                                                        class="form-select form-select-md w-100">
                                                                        <option value="">--</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="moods" class="col-12">
                                                            <div class="row g-2 g-lg-3 align-items-center">
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="col-form-label">
                                                                        {{ lang('Mood', 'home page') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col">
                                                                    <select name="mood"
                                                                        class="form-select form-select-md w-100">
                                                                        <option value="">--</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="samples" class="col-12">
                                                            <div class="row g-2 g-lg-3 align-items-center">
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="col-form-label">
                                                                        {{ lang('Samples', 'home page') }}
                                                                    </label>
                                                                </div>
                                                                <div class="col">
                                                                    <select name="samples"
                                                                        class="form-select form-select-md w-100" required>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if (auth()->user())
                                                            <div class="col-12">
                                                                <div class="row g-2 g-lg-3 align-items-center">
                                                                    <div class="col-12 col-lg-3">
                                                                        <label class="col-form-label">
                                                                            {{ lang('Visibility', 'home page') }}
                                                                        </label>
                                                                    </div>
                                                                    <div class="col">
                                                                        <select name="visibility"
                                                                            class="form-select form-select-md w-100"
                                                                            required>
                                                                            <option value="1">
                                                                                {{ lang('Public', 'home page') }}</option>
                                                                            <option value="0">
                                                                                {{ lang('Private', 'home page') }}</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-warning text-center mb-0">
                                                    {{ lang('No active engines, or your plan does not have any engines', 'home page') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                                <div class="processing d-none mt-5">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>
                                    <h5 class="mb-0">{{ lang('Generating...', 'home page') }}</h5>
                                </div>
                            @else
                                <div class="alert alert-warning text-center mt-4">
                                    <i class="fa-regular fa-circle-question me-1"></i>
                                    {{ lang('You need to have an active subscription to start generating the images', 'home page') }}
                                </div>
                            @endif
                        @else
                            <div class="text-center">
                                <a href="{{ route('login') }}"
                                    class="btn btn-primary btn-lg mt-4">{{ lang('Start Generating', 'home page') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>
    {!! ads_home_page_center() !!}
    @if ($generatedImages->count() > 0)
        <div class="section pt-0">
            <div class="container">
                <div class="section-inner">
                    <div class="section-body">
                        <div id="generated-images"
                            class="row justify-content-center row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-3 d-none">
                        </div>
                        <div id="default-images"
                            class="row justify-content-center row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-3">
                            @foreach ($generatedImages as $generatedImage)
                                <div class="col" data-aos="zoom-in" data-aos-duration="1000">
                                    <div class="ai-image">
                                        <img class="lazy" data-src="{{ $generatedImage->getThumbnailLink() }}"
                                            alt="{{ $generatedImage->prompt }}" />
                                        <div class="spinner-border"></div>
                                        <div class="ai-image-hover">
                                            <p class="mb-0">{{ $generatedImage->prompt }}</p>
                                            <div class="row g-2 alig-items-center">
                                                <div class="col">
                                                    <a href="{{ route('images.show', hashid($generatedImage->id)) }}"
                                                        class="btn btn-primary btn-md w-100">{{ lang('View Image') }}</a>
                                                </div>
                                                <div class="col-auto">
                                                    <a href="{{ route('images.download', [hashid($generatedImage->id), $generatedImage->getMainImageName()]) }}"
                                                        class="btn btn-light btn-md px-3"><i
                                                            class="fas fa-download"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="viewAllImagesButton" class="d-flex justify-content-center mt-5">
                            <a href="{{ route('images.index') }}"
                                class="btn btn-outline-primary-icon btn-lg">{{ lang('View All Generated Images', 'home page') }}
                                <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="section pt-0">
            <div class="container">
                <div class="section-inner">
                    <div class="section-body">
                        <div id="generated-images"
                            class="row justify-content-center row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-3 d-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @include('includes.features')
    @include('includes.faqs')
    @include('includes.articles')
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/aos/aos.min.css') }}">
    @endpush
    {!! ads_home_page_bottom() !!}
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/aos/aos.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/jquery/jquery.lazy.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/clipboard/clipboard.min.js') }}"></script>
    @endpush
@endsection
