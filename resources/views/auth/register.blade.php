@extends('layouts.auth')
@section('title', lang('Sign Up', 'auth'))
@section('content')
    <div class="sign-box">
        <h4>{{ lang('Sign Up', 'auth') }}</h4>
        <p class="text-muted fw-light mb-4">{{ lang('Enter your details to create an account.', 'auth') }}.</p>
        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ lang('Email address', 'forms') }}</label>
                <input type="email" name="email" class="form-control form-control-md" value="{{ old('email') }}"
                    placeholder="{{ lang('Email address', 'forms') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ lang('Password', 'forms') }}
                </label>
                <input type="password" name="password" class="form-control form-control-md"
                    placeholder="{{ lang('Password', 'forms') }}" minlength="8" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ lang('Confirm password', 'forms') }}
                </label>
                <input type="password" name="password_confirmation" class="form-control form-control-md"
                    placeholder="{{ lang('Confirm password', 'forms') }}" minlength="8" required>
            </div>
            @if ($settings->general->terms_of_service_link)
                <div class="mb-3">
                    <div class="form-check">
                        <input id="terms" name="terms" class="form-check-input" type="checkbox"
                            {{ old('terms') ? 'checked' : '' }} required>
                        <label class="form-check-label">
                            {{ lang('I agree to the', 'auth') }} <a href="{{ $settings->general->terms_of_service_link }}"
                                class="link link-primary">{{ lang('terms of service', 'auth') }}</a>
                        </label>
                    </div>
                </div>
            @endif
            <x-captcha />
            <button class="btn btn-primary btn-md w-100">{{ lang('Sign Up', 'auth') }}</button>
        </form>
        <x-oauth-buttons />
    </div>
@endsection
