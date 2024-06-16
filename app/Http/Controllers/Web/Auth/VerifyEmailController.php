<?php

namespace App\Http\Controllers\Web\Auth;

use Backpack\CRUD\app\Http\Requests\EmailVerificationRequest;
use Backpack\CRUD\app\Library\Auth\UserFromCookie;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Prologue\Alerts\Facades\Alert;

class VerifyEmailController extends Controller
{
  public null|string $redirectTo = null;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    if (!app('router')->getMiddleware()['signed'] ?? null) {
      throw new Exception('Missing "signed" alias middleware in App/Http/Kernel.php. More info: https://backpackforlaravel.com/docs/6.x/base-how-to#enable-email-verification-in-backpack-routes');
    }

    $this->middleware('signed')->only('verifyEmail');
    $this->middleware('throttle:' . config('backpack.base.email_verification_throttle_access'))->only('resendVerificationEmail');

    if (!backpack_users_have_email()) {
      abort(500, trans('backpack::base.no_email_column'));
    }
    // where to redirect after the email is verified
    $this->redirectTo = $this->redirectTo ?? route('backpack.auth.login');
  }

  public function emailVerificationRequired(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
  {
    $user = $this->getUserOrRedirect($request);

    if (is_a($user, \Illuminate\Http\RedirectResponse::class)) { // Redirect if not a user
      return $user;
    }

    return view(backpack_view('auth.verify-email'));
  }

  /**
   * Verify the user's email address.
   *
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
   */
  public function verifyEmail(EmailVerificationRequest $request)
  {
    $user = $this->getUserOrRedirect($request);
    if (is_a($user, \Illuminate\Http\RedirectResponse::class)) { // Redirect if not a user
      return $user;
    }
    $request->fulfill();

    Alert::success('Email verified successfully.')->flash();
    return redirect($this->redirectTo);
  }

  /**
   * Resend the email verification notification.
   */
  public function resendVerificationEmail(Request $request): \Illuminate\Http\RedirectResponse
  {
    $user = $this->getUserOrRedirect($request);

    if (is_a($user, \Illuminate\Http\RedirectResponse::class)) { // Redirect if not a user
      return $user;
    }

    $user->sendEmailVerificationNotification();
    Alert::success('Email verification link sent successfully.')->flash();

    return back()->with('status', 'verification-link-sent');
  }

  private function getUser(Request $request): ?\Illuminate\Contracts\Auth\MustVerifyEmail
  {
    return $request->user(backpack_guard_name()) ?? (new UserFromCookie())();
  }

  private function getUserOrRedirect(Request $request): \Illuminate\Contracts\Auth\MustVerifyEmail|\Illuminate\Http\RedirectResponse
  {
    $user = $this->getUser($request);
    if (!$user) {
      Alert::error('You are currently not logged in.')->flash();
    }
    if ($user && $user->hasVerifiedEmail()) {
      Alert::error('Your email address is already verified.')->flash();
    }
    if ($user && !$user->hasVerifiedEmail()) {
      return $user;
    }
    return redirect()->route('backpack.auth.login');
  }
}
