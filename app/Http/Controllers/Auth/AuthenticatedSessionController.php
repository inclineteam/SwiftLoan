<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Twilio\Rest\Client;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // get logged in user from auth and use user model 
        // to generate two factor code
        $user = Auth::user();
        // check first if user is null
        if ($user) {
            // generate code using mtrand
            $code = mt_rand(100000, 999999);
            // set code to user
            $user->two_factor_code = $code;
            // set expiration to 2 minutes
            $user->two_factor_expires_at = now()->addMinutes(2);
            // save user
            $user->save();

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_PHONE_NUMBER");

            // format phone number from 0912345678 to +63912345678
            $phone = '+63' . substr($user->phone, 1);
            
            $client = new Client($account_sid, $auth_token);
            $message = $client->messages->create($phone, [
                'from' => $twilio_number, 
                'body' => $user->two_factor_code]);
        }

        // this should be on otp dashboard
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
