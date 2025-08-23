<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'organization' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'in:admin,attendee'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'organization' => $request->organization,
            'role' => $request->role ?? 'attendee',
            'is_active' => true,
            'created_by' => null, // Self-registration
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Role-based redirect after registration
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'Welcome to the admin dashboard!');
        }

        return redirect()->route('events.index')->with('success', 'Welcome! You can now register for events.');
    }
}
