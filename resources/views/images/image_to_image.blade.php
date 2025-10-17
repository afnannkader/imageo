@extends('layouts.front')
@section('title', lang('Image to Image', 'images'))
@section('content')
    <div class="section my-5">
        <div class="container">
            <div class="section-inner">
                <div class="section-header text-center mb-4">
                    <h1>{{ lang('Image to Image', 'images') }}</h1>
                    <p class="text-muted mb-0">{{ lang('Upload one or more images and a prompt to generate a new image.', 'images') }}</p>
                </div>
                <div class="section-body">
                    <div class="card-v">
                        <form id="image2image-form" action="{{ route('image2image.generate') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">{{ lang('Prompt', 'images') }}</label>
                                    <textarea class="form-control" name="prompt" rows="3" placeholder="{{ lang('Describe how to transform the image...', 'images') }}" required></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ lang('Images', 'images') }}</label>
                                    <input type="file" class="form-control" name="images[]" accept="image/*" multiple />
                                    <div class="form-text">{{ lang('Supported: jpg, jpeg, png, webp. Max 5MB each.', 'images') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ lang('Engine', 'images') }}</label>
                                    <select class="form-select" name="engine">
                                        <option value="replicate:nano-banana" selected>Replicate - Nano Banana</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ lang('Output Format', 'images') }}</label>
                                    <select class="form-select" name="output_format">
                                        <option value="jpg" selected>JPG</option>
                                        <option value="png">PNG</option>
                                        <option value="webp">WEBP</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary btn-lg" type="submit">{{ lang('Generate', 'images') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-v mt-4 d-none" id="result-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="spinner-border me-2 d-none" id="loading-spinner" role="status"></div>
                            <strong id="status-text"></strong>
                        </div>
                        <div id="result-alert" class="alert d-none"></div>
                        <div id="result-content" class="d-none">
                            <div class="mb-3">
                                <h6 class="mb-2">{{ lang('Input Images', 'images') }}</h6>
                                <div class="row g-2" id="input-images"></div>
                            </div>
                            <div>
                                <h6 class="mb-2">{{ lang('Output Image', 'images') }}</h6>
                                <div id="output-image-wrap"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const form = document.getElementById('image2image-form');
                const resultCard = document.getElementById('result-card');
                const spinner = document.getElementById('loading-spinner');
                const statusText = document.getElementById('status-text');
                const resultAlert = document.getElementById('result-alert');
                const resultContent = document.getElementById('result-content');
                const inputImagesWrap = document.getElementById('input-images');
                const outputImageWrap = document.getElementById('output-image-wrap');

                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    resultCard.classList.remove('d-none');
                    resultAlert.className = 'alert d-none';
                    resultContent.classList.add('d-none');
                    outputImageWrap.innerHTML = '';
                    inputImagesWrap.innerHTML = '';
                    spinner.classList.remove('d-none');
                    statusText.textContent = '{{ lang('Processing...', 'images') }}';

                    const formData = new FormData(form);
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                            },
                            body: formData
                        });
                        const data = await res.json();
                        spinner.classList.add('d-none');
                        if (!res.ok || data.status === 'error') {
                            statusText.textContent = '{{ lang('Completed', 'images') }}';
                            resultAlert.className = 'alert alert-danger';
                            resultAlert.classList.remove('d-none');
                            resultAlert.textContent = data.message || 'Error';
                            return;
                        }

                        statusText.textContent = '{{ lang('Completed', 'images') }}';
                        resultContent.classList.remove('d-none');

                        // Input images
                        if (Array.isArray(data.input_images)) {
                            data.input_images.forEach(function(url) {
                                const col = document.createElement('div');
                                col.className = 'col-auto';
                                col.innerHTML = '<img src="' + url + '" class="img-thumbnail" style="max-height:120px">';
                                inputImagesWrap.appendChild(col);
                            });
                        }

                        // Output image
                        if (data.output_image) {
                            outputImageWrap.innerHTML = '<a href="' + (data.view_link || '#') + '" target="_blank"><img src="' + data.output_image + '" class="img-fluid"></a>';
                        }
                    } catch (err) {
                        spinner.classList.add('d-none');
                        statusText.textContent = '{{ lang('Completed', 'images') }}';
                        resultAlert.className = 'alert alert-danger';
                        resultAlert.classList.remove('d-none');
                        resultAlert.textContent = err.message || 'Error';
                    }
                });
            })();
        </script>
    @endpush
@endsection


