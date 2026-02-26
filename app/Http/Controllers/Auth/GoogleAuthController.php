<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $next = $request->query('next');
        $forceAccount = $request->boolean('force_account');

        if (is_string($next) && $next !== '') {
            $request->session()->put('google_auth_next', $next);
        }

        if ($forceAccount && Auth::check()) {
            Auth::logout();
        }

        $driver = Socialite::driver('google');

        if ($forceAccount) {
            $driver = $driver->with(['prompt' => 'select_account']);
        }

        return $driver->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::query()->firstWhere('email', $googleUser->getEmail());

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Alumno',
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(40)),
                'role' => 'student',
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $next = $request->session()->pull('google_auth_next');

        if (is_string($next) && $next !== '') {
            return redirect()->to($next);
        }

        if ($user->isProfessor()) {
            return redirect()->route('dashboard');
        }

        return redirect('/');
    }
}
