<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\UserSession;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nim'      => ['required', 'numeric'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            UserSession::create([
                'user_id'    => Auth::id(),
                'nim'        => Auth::user()->nim,
                'name'       => Auth::user()->name,
                'session_id' => Session::getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('foods.index'));
        }

        return back()->withErrors(['nim' => 'Invalid NIM or password.'])->onlyInput('nim');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nim'                  => ['required', 'numeric', 'unique:users,nim'],
            'name'                 => ['required', 'string', 'max:255'],
            'password'             => ['required', 'min:6', 'confirmed'],
            'password_confirmation'=> ['required'],
        ]);

        User::create([
            'nim'      => $request->nim,
            'name'     => $request->name,
            'password' => $request->password,
        ]);

        return redirect()->route('login')->with('success', 'Account created successfully. Please log in.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'nim'                   => ['required', 'numeric'],
            'name'                  => ['required', 'string'],
            'password'              => ['required', 'min:6', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = User::where('nim', $request->nim)
                    ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
                    ->first();

        if (! $user) {
            return back()->withErrors(['nim' => 'No account found with that NIM and name.'])->onlyInput('nim', 'name');
        }

        $user->update(['password' => $request->password]);

        return redirect()->route('login')->with('success', 'Password reset successfully. Please log in.');
    }

    public function logout(Request $request)
    {
        DB::table('user_sessions')
          ->where('session_id', Session::getId())
          ->update(['is_active' => false]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
