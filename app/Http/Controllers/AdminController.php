<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Notification;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin dashboard — overview stats.
     */
    public function dashboard()
    {
        $stats = [
            'total_users'             => User::count(),
            'total_providers'         => User::where('role', 'provider')->count(),
            'total_customers'         => User::where('role', 'customer')->count(),
            'pending_providers'       => ProviderProfile::where('is_approved', false)->count(),
            'total_services'          => Service::count(),
            'total_bookings'          => Booking::count(),
            'pending_bookings'        => Booking::where('status', 'pending')->count(),
            'completed_bookings'      => Booking::where('status', 'completed')->count(),
        ];

        $recentBookings = Booking::with(['customer', 'provider', 'service'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }

    /**
     * Approve or reject a provider application.
     */
    public function approveProvider(Request $request, User $provider)
    {
        if ($provider->role !== 'provider') {
            abort(404);
        }

        $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        $profile = $provider->providerProfile;

        if (! $profile) {
            abort(404, 'Provider profile not found.');
        }

        $approved = $request->input('action') === 'approve';
        $profile->update(['is_approved' => $approved]);

        // Notify the provider
        Notification::create([
            'user_id' => $provider->id,
            'title'   => $approved ? 'Account Approved' : 'Account Rejected',
            'message' => $approved
                ? 'Congratulations! Your provider account has been approved. You can now list services.'
                : 'Unfortunately, your provider application has been rejected. Please contact support for more information.',
            'type'    => $approved ? 'account_approved' : 'account_rejected',
        ]);

        return back()->with('success', 'Provider ' . ($approved ? 'approved' : 'rejected') . ' successfully.');
    }

    /**
     * List all providers pending approval.
     */
    public function pendingProviders()
    {
        $providers = User::with('providerProfile')
            ->where('role', 'provider')
            ->whereHas('providerProfile', fn ($q) => $q->where('is_approved', false))
            ->latest()
            ->paginate(20);

        return view('admin.providers', compact('providers'));
    }

    /**
     * CRUD for categories.
     */
    public function manageCategories()
    {
        $categories = Category::withCount('services')->latest()->paginate(20);
        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'icon' => ['nullable', 'string', 'max:100'],
        ]);

        Category::create($request->only('name', 'icon'));

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'icon' => ['nullable', 'string', 'max:100'],
        ]);

        $category->update($request->only('name', 'icon'));

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    /**
     * View all bookings across the platform.
     */
    public function allBookings(Request $request)
    {
        $query = Booking::with(['customer', 'provider', 'service']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        return view('admin.bookings', compact('bookings'));
    }
}
