<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Customer creates a booking.
     * Checks availability + double-booking inside a DB transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'   => ['required', 'exists:services,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot'    => ['required', 'date_format:H:i'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // Determine day of week from booking_date
        $dayOfWeek = strtolower(date('l', strtotime($validated['booking_date'])));

        try {
            $booking = DB::transaction(function () use ($validated, $service, $dayOfWeek) {

                // 1. Check provider availability for that day
                $slot = Availability::where('provider_id', $service->provider_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->first();

                if (! $slot) {
                    throw new \Exception("Provider is not available on {$dayOfWeek}.");
                }

                // 2. Verify time_slot falls within start_time–end_time
                if (
                    $validated['time_slot'] < $slot->start_time ||
                    $validated['time_slot'] >= $slot->end_time
                ) {
                    throw new \Exception(
                        "Requested time is outside provider's available hours ({$slot->start_time} – {$slot->end_time})."
                    );
                }

                // 3. Check for double-booking (pending or confirmed)
                $conflict = Booking::where('provider_id', $service->provider_id)
                    ->where('booking_date', $validated['booking_date'])
                    ->where('time_slot', $validated['time_slot'])
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    throw new \Exception('This time slot is already booked. Please choose another.');
                }

                // 4. Create the booking
                $booking = Booking::create([
                    'customer_id'  => Auth::id(),
                    'provider_id'  => $service->provider_id,
                    'service_id'   => $validated['service_id'],
                    'booking_date' => $validated['booking_date'],
                    'time_slot'    => $validated['time_slot'],
                    'status'       => 'pending',
                    'notes'        => $validated['notes'] ?? null,
                ]);

                // 5. Notify the provider
                Notification::create([
                    'user_id' => $service->provider_id,
                    'title'   => 'New Booking Request',
                    'message' => Auth::user()->name . ' has requested a booking for "' . $service->title . '" on ' . $validated['booking_date'] . ' at ' . $validated['time_slot'] . '.',
                    'type'    => 'booking_request',
                ]);

                return $booking;
            });

        } catch (\Exception $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        return redirect()->route('customer.bookings')
            ->with('success', 'Booking request sent successfully.');
    }

    /**
     * List bookings for the authenticated user.
     * Customer sees their own bookings; provider sees incoming requests.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'customer') {
            $bookings = Booking::with(['service', 'provider', 'review'])
                ->where('customer_id', $user->id)
                ->latest()
                ->paginate(15);
        } elseif ($user->role === 'provider') {
            $bookings = Booking::with(['service', 'customer', 'review'])
                ->where('provider_id', $user->id)
                ->latest()
                ->paginate(15);
        } else {
            // Admin — all bookings
            $bookings = Booking::with(['service', 'customer', 'provider'])
                ->latest()
                ->paginate(20);
        }

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Provider confirms or cancels a booking; admin can also cancel.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $user = Auth::user();

        $request->validate([
            'status' => ['required', 'in:confirmed,cancelled'],
        ]);

        // Only the provider or admin may change status
        if ($user->role === 'provider' && $booking->provider_id !== $user->id) {
            abort(403);
        }

        if ($user->role === 'customer') {
            abort(403);
        }

        $oldStatus = $booking->status;
        $newStatus = $request->input('status');

        $booking->update(['status' => $newStatus]);

        // Notify the customer
        $message = $newStatus === 'confirmed'
            ? 'Your booking for "' . $booking->service->title . '" on ' . $booking->booking_date->format('Y-m-d') . ' has been confirmed.'
            : 'Your booking for "' . $booking->service->title . '" on ' . $booking->booking_date->format('Y-m-d') . ' has been cancelled.';

        Notification::create([
            'user_id' => $booking->customer_id,
            'title'   => $newStatus === 'confirmed' ? 'Booking Confirmed' : 'Booking Cancelled',
            'message' => $message,
            'type'    => $newStatus === 'confirmed' ? 'booking_confirmed' : 'booking_cancelled',
        ]);

        return back()->with('success', 'Booking status updated to ' . $newStatus . '.');
    }

    /**
     * Mark a booking as completed (provider action).
     */
    public function complete(Booking $booking)
    {
        $user = Auth::user();

        if ($user->role === 'provider' && $booking->provider_id !== $user->id) {
            abort(403);
        }

        if ($booking->status !== 'confirmed') {
            return back()->withErrors(['booking' => 'Only confirmed bookings can be marked as completed.']);
        }

        $booking->update(['status' => 'completed']);

        // Notify the customer they can now leave a review
        Notification::create([
            'user_id' => $booking->customer_id,
            'title'   => 'Service Completed',
            'message' => 'Your booking for "' . $booking->service->title . '" has been marked as completed. Please leave a review!',
            'type'    => 'booking_completed',
        ]);

        return back()->with('success', 'Booking marked as completed.');
    }
}
