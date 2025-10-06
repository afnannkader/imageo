<!DOCTYPE html>
<html lang="{{ getLang() }}">

<head>
    @include('global.head')
    @push('styles')
        <link rel="stylesheet" href="{{ assetWithVersion('assets/main/css/app.css') }}">
    @endpush
    @include('global.styles')
    {!! head_code() !!}
</head>

<body>
    @include('includes.navbar')
    @yield('content')
    @include('includes.footer')
    @push('scripts')
        <script src="{{ assetWithVersion('assets/main/js/app.js') }}"></script>
    @endpush
    @include('configurations.config')
    @include('configurations.widgets')
    @include('global.scripts')
</body>

</html>
