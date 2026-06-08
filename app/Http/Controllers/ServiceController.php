<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Public listing — all active services with optional filters.
     */
    public function index(Request $request)
    {
        $categories = Category::all();

        $query = Service::with(['provider', 'provider.providerProfile', 'category'])
            ->where('is_active', true)
            ->whereHas('provider.providerProfile', fn ($q) => $q->where('is_approved', true));

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('city')) {
            $city = $request->input('city');
            $query->whereHas('provider', fn ($q) => $q->where('city', 'like', "%{$city}%"));
        }

        if ($request->filled('price_type')) {
            $query->where('price_type', $request->input('price_type'));
        }

        $services = $query->latest()->paginate(15)->withQueryString();

        return view('services.index', compact('services', 'categories'));
    }

    /**
     * Public — single service detail with provider info and reviews.
     */
    public function show(Service $service)
    {
        $service->load([
            'provider',
            'provider.providerProfile',
            'category',
            'bookings.review',
        ]);

        $reviews = \App\Models\Review::with('customer')
            ->where('provider_id', $service->provider_id)
            ->latest()
            ->take(10)
            ->get();

        return view('services.show', compact('service', 'reviews'));
    }

    /**
     * Provider — create a new service.
     */
    public function store(Request $request)
    {
        $this->authorizeProvider();

        $validated = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'price'        => ['required', 'numeric', 'min:0'],
            'price_type'   => ['required', 'in:hourly,fixed'],
        ]);

        $service = Service::create([
            ...$validated,
            'provider_id' => Auth::id(),
            'is_active'   => true,
        ]);

        return redirect()->route('provider.dashboard')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Provider — update an existing service.
     */
    public function update(Request $request, Service $service)
    {
        $this->authorizeProvider();
        $this->authorizeOwner($service);

        $validated = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'price'        => ['required', 'numeric', 'min:0'],
            'price_type'   => ['required', 'in:hourly,fixed'],
            'is_active'    => ['boolean'],
        ]);

        $service->update($validated);

        return redirect()->route('provider.dashboard')
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Provider — delete a service.
     */
    public function destroy(Service $service)
    {
        $this->authorizeProvider();
        $this->authorizeOwner($service);

        $service->delete();

        return redirect()->route('provider.dashboard')
            ->with('success', 'Service deleted.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function authorizeProvider(): void
    {
        if (Auth::user()->role !== 'provider') {
            abort(403);
        }
    }

    private function authorizeOwner(Service $service): void
    {
        if ($service->provider_id !== Auth::id()) {
            abort(403, 'You do not own this service.');
        }
    }
}
