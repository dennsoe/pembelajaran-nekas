<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['profile', 'email'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if email domain is belajar.id
            $email = $googleUser->getEmail();
            if (!str_ends_with($email, '@belajar.id')) {
                return redirect('/login')->with('error', 'Hanya email dengan domain @belajar.id yang diperbolehkan');
            }

            // Check if user exists
            $user = User::where('email', $email)->first();

            if (!$user) {
                // If user doesn't exist, show error (admin must register first)
                return redirect('/login')->with('error', 'Akun Anda belum terdaftar. Silakan hubungi admin untuk registrasi.');
            }

            // Check if user is active
            if (!$user->is_active) {
                return redirect('/login')->with('error', 'Akun Anda tidak aktif. Silakan hubungi admin.');
            }

            // Check if user is a teacher (guru)
            if ($user->group !== 'guru') {
                return redirect('/login')->with('error', 'Google OAuth hanya untuk akun guru.');
            }

            // Update Google ID if not set
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                ]);
            }

            // Log the user in
            Auth::login($user, true);

            // Redirect to dashboard
            return redirect()->route('guru.dashboard')->with('success', 'Login berhasil!');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login dengan Google gagal: ' . $e->getMessage());
        }
    }
}
