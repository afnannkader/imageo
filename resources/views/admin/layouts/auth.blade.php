<!DOCTYPE html>
<html lang="{{ getLang() }}">

<head>
    @include('admin.includes.head')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/fontawesome/fontawesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/vironeer/toastr/css/vironeer-toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extra/css/colors.css') }}">
    <link rel="stylesheet" href="{{ assetWithVersion('assets/vendor/admin/css/app.css') }}" />
</head>

<body>
    <div class="vironeer-sign-container">
        <div class="vironeer-sign-form">
            <a href="{{ route('admin.index') }}" class="vironeer-sign-logo">
                <img src="{{ asset($settings->media->dark_logo) }}" alt="{{ $settings->general->site_name }}" />
            </a>
            <div class="card">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/vironeer/toastr/js/vironeer-toastr.min.js') }}"></script>
    <script src="{{ assetWithVersion('assets/vendor/admin/js/app.js') }}"></script>
    @toastrRender
    @if ($errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}')
            @endforeach
        </script>
    @elseif(session('status'))
        <script>
            toastr.success('{{ session('status') }}')
        </script>
    @endif
</body>

</html>
