<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\ProviderProfile;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Customer submits a review after a completed booking.
     * Enforces one review per booking and recalculates provider avg_rating.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        // Only the customer of this booking may review
        if ($booking->customer_id !== Auth::id()) {
            abort(403, 'You can only review your own bookings.');
        }

        // Booking must be completed
        if ($booking->status !== 'completed') {
            return back()->withErrors(['review' => 'You can only review completed bookings.']);
        }

        // One review per booking (unique constraint also enforced at DB level)
        if ($booking->review()->exists()) {
            return back()->withErrors(['review' => 'You have already reviewed this booking.']);
        }

        $review = Review::create([
            'booking_id'  => $booking->id,
            'customer_id' => Auth::id(),
            'provider_id' => $booking->provider_id,
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'] ?? null,
        ]);

        // Recalculate provider avg_rating and total_reviews
        $this->recalculateProviderRating($booking->provider_id);

        // Notify the provider
        Notification::create([
            'user_id' => $booking->provider_id,
            'title'   => 'New Review Received',
            'message' => Auth::user()->name . ' left a ' . $validated['rating'] . '-star review for your service.',
            'type'    => 'new_review',
        ]);

        return back()->with('success', 'Review submitted successfully. Thank you!');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function recalculateProviderRating(int $providerId): void
    {
        $stats = Review::where('provider_id', $providerId)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        ProviderProfile::where('user_id', $providerId)->update([
            'avg_rating'    => round($stats->avg_rating, 2),
            'total_reviews' => $stats->total_reviews,
        ]);
    }
}
