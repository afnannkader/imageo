<!DOCTYPE html>
<html lang="{{ getLang() }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="no index">
    <title>{{ $settings->maintenance->title }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/fontawesome/fontawesome.min.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4"><i class="fa-regular fa-clock fa-3x"></i></h1>
                <h1>{{ $settings->maintenance->title }}</h1>
                <div>
                    {!! $settings->maintenance->body !!}
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
