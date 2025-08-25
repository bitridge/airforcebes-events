<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists
            if ($user->profile_picture) {
                \Storage::disk('public')->delete($user->profile_picture);
            }
            
            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }
        
        // Update other fields
        $user->fill($request->except('profile_picture'));

        // Ensure name is constructed from first_name and last_name
        if ($request->filled('first_name') && $request->filled('last_name')) {
            $user->name = $request->first_name . ' ' . $request->last_name;
        }

        // Handle boolean fields
        $user->small_business_forum = $request->boolean('small_business_forum');
        $user->small_business_matchmaker = $request->boolean('small_business_matchmaker');

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Show the user's profile details.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        
        return view('profile.show', [
            'user' => $user,
            'upcomingRegistrations' => $user->getUpcomingRegistrations(),
            'pastRegistrations' => $user->getPastRegistrations(),
            'registrationCount' => $user->getRegistrationCount(),
            'checkInCount' => $user->getCheckInCount(),
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Check if user has any upcoming registrations
        $upcomingRegistrations = $user->getUpcomingRegistrations();
        if ($upcomingRegistrations->count() > 0) {
            return Redirect::route('profile.edit')->withErrors([
                'account_deletion' => 'You cannot delete your account while you have upcoming event registrations. Please cancel your registrations first.'
            ]);
        }

        Auth::logout();

        // Instead of deleting, deactivate the user to preserve data integrity
        $user->update([
            'is_active' => false,
            'email' => $user->email . '_deleted_' . time(),
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Your account has been successfully deactivated.');
    }
}
