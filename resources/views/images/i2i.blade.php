@extends('layouts.front')
@section('title', lang('Image to Image Generator', 'images'))
@section('content')
    {!! ads_home_page_top() !!} <header class="header my-5">
        <div class="container">
            <h1 class="mb-5 text-center">{{ lang('Image to Image Generator', 'images') }}</h1>
            <div class="card-v">
                <form id="img2imgForm" enctype="multipart/form-data" method="POST"
                    action="{{ route('images.image_to_image.generate') }}"> @csrf {{-- Hidden input for NanoBanana engine --}} @php $nanoEngine = \App\Models\Engine::where('alias', 'nano-banana')->where('status', 1)->first(); @endphp
                    @if ($nanoEngine)
                        <input type="hidden" name="engine" value="{{ $nanoEngine->alias }}">
                    @endif <input type="hidden" name="size" value="1024x1024"> <input
                        type="hidden" name="samples" value="1">
                    <div class="row g-3">
                        <div class="col-lg-6"> <label
                                class="form-label">{{ lang('Upload Base Image', 'home-page') }}</label> <input
                                type="file" name="image" accept="image/*" required class="form-control"> </div>
                        <div class="col-lg-6"> <label class="form-label">{{ lang('Prompt', 'home-page') }}</label>
                            <input type="text" name="prompt" required class="form-control"
                                placeholder="{{ lang('Describe your modification...', 'home-page') }}">
                        </div>
                    </div>
                    <div class="text-center mt-4"> <button type="submit" class="btn btn-primary px-5"> <i
                                class="fa-solid fa-rotate me-2"></i>{{ lang('Generate', 'home-page') }} </button> </div>
                </form>
            </div>
        </div>
    </header>
    <div class="processing d-none mt-5 text-center">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <h5 class="mb-0">{{ lang('Processing...', 'home-page') }}</h5>
    </div>
    <div id="result" class="mt-5 container">
        @if (isset($generatedImages) && $generatedImages->count() > 0)
            <div class="row justify-content-center row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-3">
                @foreach ($generatedImages as $generatedImage)
                    <div class="col" data-aos="zoom-in" data-aos-duration="1000">
                        <div class="ai-image"> <img class="lazy" data-src="{{ $generatedImage->getThumbnailLink() }}"
                                alt="{{ $generatedImage->prompt }}" />
                            <div class="spinner-border"></div>
                            <div class="ai-image-hover">
                                <p class="mb-0">{{ $generatedImage->prompt }}</p>
                                <div class="row g-2 alig-items-center">
                                    <div class="col"> <a href="{{ route('images.show', hashid($generatedImage->id)) }}"
                                            target="_blank"
                                            class="btn btn-primary btn-md w-100">{{ lang('View Image') }}</a> </div>
                                    <div class="col-auto"> <a
                                            href="{{ route('images.download', [hashid($generatedImage->id), $generatedImage->getMainImageName()]) }}"
                                            class="btn btn-light btn-md px-3"><i class="fas fa-download"></i></a> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-5"> {{ $generatedImages->appends(request()->input())->links() }} </div>
        @endif
    </div>
    @include('includes.faqs')
    @include('includes.articles') {!! ads_home_page_bottom() !!}
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/aos/aos.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/aos/aos.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/jquery/jquery.lazy.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/clipboard/clipboard.min.js') }}"></script>
    @endpush
    @push('scripts')
        <script>
            document.getElementById('img2imgForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const processingDiv = document.querySelector('.processing');
                const resultDiv = document.getElementById('result');

                processingDiv.classList.remove('d-none');

                try {
                    const response = await fetch('{{ route('images.image_to_image.generate') }}', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    processingDiv.classList.add('d-none');

                    if (result.error) {
                        resultDiv.innerHTML = `<p class="text-danger">${result.error}</p>`;
                        return;
                    }

                    let html = '';
                    result.images.forEach(img => {
                        html += `
                <div class="mb-3 text-center">
                    <img src="${img.src}" alt="Generated Image" class="img-fluid mb-2">
                    <div>
                        <a href="${img.download_link}" download class="btn btn-sm btn-outline-primary">
                            Download
                        </a>
                    </div>
                </div>
            `;
                    });
                    resultDiv.innerHTML = html;

                } catch (error) {
                    processingDiv.classList.add('d-none');
                    resultDiv.innerHTML = `<p class="text-danger">Something went wrong. Please try again.</p>`;
                    console.error(error);
                }
            });
        </script>
    @endpush

@endsection
