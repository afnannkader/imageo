@if ($oauthProviders->count() > 0)
    <div class="login-with mt-3">
        <div class="login-with-divider mb-3">
            <span>{{ lang('Or With', 'auth') }}</span>
        </div>
        <div class="row row-cols-1 row-cols-sm-{{ $oauthProviders->count() > 1 ? 2 : 1 }} g-3">
            @foreach ($oauthProviders as $oauthProvider)
                <div class="col">
                    <a href="{{ route('oauth.login', $oauthProvider->alias) }}"
                        class="btn btn-social btn-md w-100 text-center">
                        <img src="{{ asset($oauthProvider->logo) }}" class="me-1" width="25px" height="25px">
                        {{ lang($oauthProvider->name, 'auth') }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
