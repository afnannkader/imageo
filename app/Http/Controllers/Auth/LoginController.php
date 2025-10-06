<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Methods\ReCaptchaValidation;
use App\Models\User;
use App\Models\UserLog;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::USER;
    protected $providers = ['facebook'];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Create a log or update an exists one
     *
     * @return void
     */
    public static function setLog($user)
    {
        $ip = ipInfo()->ip;
        $loginLog = UserLog::where([['user_id', $user->id], ['ip', $ip]])->first();
        $location = ipInfo()->location->city . ', ' . ipInfo()->location->country;
        if ($loginLog != null) {
            $loginLog->country = ipInfo()->location->country;
            $loginLog->country_code = ipInfo()->location->country_code;
            $loginLog->timezone = ipInfo()->location->timezone;
            $loginLog->location = $location;
            $loginLog->latitude = ipInfo()->location->latitude;
            $loginLog->longitude = ipInfo()->location->longitude;
            $loginLog->browser = ipInfo()->system->browser;
            $loginLog->os = ipInfo()->system->os;
            $loginLog->update();
        } else {
            $newLoginLog = new UserLog();
            $newLoginLog->user_id = $user->id;
            $newLoginLog->ip = ipInfo()->ip;
            $newLoginLog->country = ipInfo()->location->country;
            $newLoginLog->country_code = ipInfo()->location->country_code;
            $newLoginLog->timezone = ipInfo()->location->timezone;
            $newLoginLog->location = $location;
            $newLoginLog->latitude = ipInfo()->location->latitude;
            $newLoginLog->longitude = ipInfo()->location->longitude;
            $newLoginLog->browser = ipInfo()->system->browser;
            $newLoginLog->os = ipInfo()->system->os;
            $newLoginLog->save();
        }
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ] + app(ReCaptchaValidation::class)->validate());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if (authUser()->status == 0) {
            Auth::logout();
            toastr()->error(lang('Your account has been blocked', 'auth'));
            return redirect()->route('login');
        }

        self::setLog($user);
    }

    /**
     * Log the admin out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $sessionKey = $this->guard()->user()->name;

        $this->guard()->logout();

        $request->session()->forget($sessionKey);

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
        ? new JsonResponse([], 204)
        : redirect()->route('home');
    }
}