@extends('admin.layouts.auth')
@section('section', admin_lang('Admin'))
@section('title', admin_lang('2Fa Verification'))
@section('content')
    <h1 class="mb-0 h3">{{ admin_lang('2Fa Verification') }}</h1>
    <p class="card-text text-muted">{{ admin_lang('Please enter the OTP code to continue') }}</p>
    <form action="{{ route('admin.2fa.verify') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ admin_lang('OTP Code') }} </label>
            <input type="text" name="otp_code" class="form-control form-control-lg input-numeric" placeholder="• • • • • •"
                maxlength="6" required>
        </div>
        <button class="btn btn-primary btn-lg d-block w-100">{{ admin_lang('Continue') }}</button>
    </form>
@endsection
