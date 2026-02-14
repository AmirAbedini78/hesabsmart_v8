<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\Facades\ReCaptcha;
use Modules\Core\Rules\ValidRecaptchaRule;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        AuthenticatesUsers::logout as baseLogout;
    }

    /**
     * The possible assets extensions.
     *
     * @var string[]
     */
    protected $assetsExtensions = ['.js', '.css', '.ico', '.png', '.jpg', '.jpeg'];

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
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth::login');
    }

    /**
     * Validate the user login request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
            ...ReCaptcha::shouldShow() ?
                ['g-recaptcha-response' => ['required', new ValidRecaptchaRule]] :
                [],
        ]);
    }

    /**
     * The user has been authenticated.
     *
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function authenticated(Request $request, $user)
    {
        $request->session()->put('locale', $user->preferredLocale());

        $response = redirect()->intended($this->redirectPath());

        if (! $request->wantsJson()) {
            return $response;
        }

        $redirectTo = Str::endsWith($response->getTargetUrl(), $this->assetsExtensions) ?
            $this->redirectPath() :
            $response->getTargetUrl();

        return new JsonResponse([
            'redirect_path' => $redirectTo,
        ], 200);
    }

    /**
     * Porivde the path the user should be redirected after login.
     */
    protected function redirectTo(): string
    {
        return auth()->user()->landingPage();
    }

    /**
     * Get the failed login response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth::auth.failed')],
        ]);
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => [trans('auth::auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        $response = $this->baseLogout($request);

        $request->session()->put('locale', $user->preferredLocale());

        return $response;
    }
}
