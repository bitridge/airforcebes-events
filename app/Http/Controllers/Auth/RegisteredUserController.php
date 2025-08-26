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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'organization_name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'naics_codes' => ['nullable', 'string', 'max:1000'],
            'industry_connections' => ['nullable', 'string', 'max:1000'],
            'core_specialty_area' => ['nullable', 'string', 'max:1000'],
            'contract_vehicles' => ['nullable', 'string', 'max:1000'],
            'meeting_preference' => ['required', 'string', 'max:255'],
            'small_business_forum' => ['nullable', 'string', 'in:Yes (In-person),No'],
            'small_business_matchmaker' => ['nullable', 'string', 'in:Yes (In-person),No'],
            'role' => ['required', 'in:admin,attendee'],
        ]);

        // Generate full name from first and last name
        $fullName = trim($request->first_name . ' ' . $request->last_name);

        $user = User::create([
            'name' => $fullName,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'organization' => $request->organization_name, // Keep for backward compatibility
            'organization_name' => $request->organization_name,
            'title' => $request->title,
            'naics_codes' => $request->naics_codes,
            'industry_connections' => $request->industry_connections,
            'core_specialty_area' => $request->core_specialty_area,
            'contract_vehicles' => $request->contract_vehicles,
            'meeting_preference' => $request->meeting_preference,
            'small_business_forum' => $request->small_business_forum,
            'small_business_matchmaker' => $request->small_business_matchmaker,
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
