<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class CustomerAuthController extends Controller
{
    // ------------------------------------------------------------
    // Registration
    // ------------------------------------------------------------
    public function showRegister()
    {
        if (Auth::check() && !Auth::user()->is_admin) {
            return redirect()->route('customer.dashboard');
        }
        return view('store.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/', 'unique:users,phone'],
            'address'  => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => strtolower(trim($request->input('email'))),
            'phone'    => $request->input('phone'),
            'address'  => $request->input('address'),
            'password' => $request->input('password'),
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')
            ->with('success', 'Welcome ' . $user->name . '! Your account has been created successfully.');
    }

    // ------------------------------------------------------------
    // Login (email OR phone)
    // ------------------------------------------------------------
    public function showLogin()
    {
        if (Auth::check() && !Auth::user()->is_admin) {
            return redirect()->route('customer.dashboard');
        }
        return view('store.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);
        $user = User::where('email', $login)->orWhere('phone', $login)->first();

        if ($user && !$user->is_admin && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($request->boolean('checkout_redirect')) {
                return redirect()->route('checkout');
            }

            return redirect()->intended(route('customer.dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'login' => 'These credentials do not match our records.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }

    // ------------------------------------------------------------
    // Forgot / Reset Password
    // ------------------------------------------------------------
    public function showForgotPassword()
    {
        return view('store.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
        ]);

        $login = trim($request->input('login'));
        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->where('is_admin', false)
            ->first();

        if ($user && $user->email) {
            Password::broker()->sendResetLink(['email' => $user->email]);
        }

        // Always show the same message to avoid leaking which accounts exist.
        return back()->with('success', 'If an account exists with that email or phone number, a password reset link has been sent to the email on file.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('store.reset-password', [
            'token' => $token,
            'login' => $request->query('login'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'login'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('email', $request->input('login'))
            ->orWhere('phone', $request->input('login'))
            ->first();

        if (!$user || !$user->email) {
            return back()->withErrors([
                'login' => 'Unable to find an account matching that email or phone number.',
            ]);
        }

        $status = Password::broker()->reset(
            [
                'email'                 => $user->email,
                'password'              => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
                'token'                 => $request->input('token'),
            ],
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->setRememberToken(\Illuminate\Support\Str::random(60));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('customer.login')
                ->with('success', 'Your password has been reset. Please log in with your new password.');
        }

        return back()->withErrors(['login' => __($status)]);
    }

    // ------------------------------------------------------------
    // Account Dashboard & Profile
    // ------------------------------------------------------------
    public function dashboard()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('deliveryCharge')->withCount('items')->latest()->paginate(10);

        return view('store.account.dashboard', compact('user', 'orders'));
    }

    public function editProfile()
    {
        return view('store.account.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'           => ['required', 'string', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/', 'unique:users,phone,' . $user->id],
            'address'         => ['nullable', 'string', 'max:1000'],
            'current_password' => ['nullable', 'current_password:web', 'required_with:new_password'],
            'new_password'    => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name    = $request->input('name');
        $user->email   = strtolower(trim($request->input('email')));
        $user->phone   = $request->input('phone');
        $user->address = $request->input('address');

        if ($request->filled('new_password')) {
            $user->password = $request->input('new_password');
        }

        $user->save();

        return redirect()->route('customer.profile.edit')
            ->with('success', 'Your profile has been updated successfully.');
    }
}