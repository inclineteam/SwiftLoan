<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// otp verification
Route::get('/otp-verification', function () {
    return Inertia::render('Auth/OTPVerification');
})->middleware(['auth', 'verified'])->name('otp-verification');

// otp store
Route::post('/otp-verification', function () {
    $user = Auth::user();

    // check if otp is expired
    if ($user->two_factor_expires_at->lt(now())) {
        // reset two factor and logout user send error to login that otp has expired
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        auth()->logout();
        return redirect()->route('login')->with([
            'otp' => 'The provided code has expired.',
        ]);
    }
    
    // check if otp is the same with the user otp
    if (request('otp') != $user->two_factor_code) {
        return back()->with([
            'otp' => 'The provided code does not match the code in our records.',
        ]);
    }
    // update user otp
    $user->two_factor_code = null;
    $user->two_factor_expires_at = null;
    $user->save();
    
    // dashboard
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified'])->name('otp-verification.store');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'twofactor'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
