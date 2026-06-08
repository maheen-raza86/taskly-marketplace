<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderController extends Controller
{
    /**
     * Public provider profile — services and reviews.
     */
    public function profile(User $provider)
    {
        if ($provider->role !== 'provider') {
            abort(404);
        }

        $provider->load([
            'providerProfile',
            'services' => fn ($q) => $q->where('is_active', true)->with('category'),
        ]);

        $reviews = Review::with('customer')
            ->where('provider_id', $provider->id)
            ->latest()
            ->paginate(10);

        return view('provider.profile', compact('provider', 'reviews'));
    }

    /**
     * Provider dashboard — pending and confirmed bookings.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $pendingBookings = Booking::with(['customer', 'service'])
            ->where('provider_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $confirmedBookings = Booking::with(['customer', 'service'])
            ->where('provider_id', $user->id)
            ->where('status', 'confirmed')
            ->latest()
            ->get();

        $services = $user->services()->with('category')->get();

        $profile = $user->providerProfile;

        return view('provider.dashboard', compact(
            'pendingBookings',
            'confirmedBookings',
            'services',
            'profile'
        ));
    }

    /**
     * Provider sets their weekly availability.
     * Replaces all existing slots for the provider.
     */
    public function setAvailability(Request $request)
    {
        $request->validate([
            'availability'                  => ['required', 'array'],
            'availability.*.day_of_week'    => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'availability.*.start_time'     => ['required', 'date_format:H:i'],
            'availability.*.end_time'       => ['required', 'date_format:H:i', 'after:availability.*.start_time'],
        ]);

        $providerId = Auth::id();

        // Delete existing availability and replace
        Availability::where('provider_id', $providerId)->delete();

        foreach ($request->input('availability') as $slot) {
            Availability::create([
                'provider_id' => $providerId,
                'day_of_week' => $slot['day_of_week'],
                'start_time'  => $slot['start_time'],
                'end_time'    => $slot['end_time'],
            ]);
        }

        return back()->with('success', 'Availability updated successfully.');
    }
}
