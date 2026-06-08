<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Home page — search/filter services by city, category, keyword.
     */
    public function index(Request $request)
    {
        $categories = Category::all();

        $query = Service::with(['provider', 'provider.providerProfile', 'category'])
            ->where('is_active', true)
            ->whereHas('provider.providerProfile', fn ($q) => $q->where('is_approved', true));

        // Filter by keyword (title or description)
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by provider city
        if ($request->filled('city')) {
            $city = $request->input('city');
            $query->whereHas('provider', fn ($q) => $q->where('city', 'like', "%{$city}%"));
        }

        $services = $query->latest()->paginate(12)->withQueryString();

        return view('home', compact('services', 'categories'));
    }
}
