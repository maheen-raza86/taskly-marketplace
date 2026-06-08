<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Providers ─────────────────────────────────────────────────────────

        $provider1 = User::updateOrCreate(
            ['email' => 'provider1@marketplace.com'],
            [
                'name'     => 'Usman Khan',
                'password' => Hash::make('password'),
                'role'     => 'provider',
                'city'     => 'Lahore',
                'phone'    => '03111111111',
            ]
        );

        ProviderProfile::updateOrCreate(
            ['user_id' => $provider1->id],
            [
                'bio'              => 'Experienced plumber with 8 years of residential and commercial work across Lahore.',
                'experience_years' => 8,
                'is_approved'      => true,
                'avg_rating'       => 4.50,
                'total_reviews'    => 12,
            ]
        );

        $provider2 = User::updateOrCreate(
            ['email' => 'provider2@marketplace.com'],
            [
                'name'     => 'Ayesha Malik',
                'password' => Hash::make('password'),
                'role'     => 'provider',
                'city'     => 'Karachi',
                'phone'    => '03222222222',
            ]
        );

        ProviderProfile::updateOrCreate(
            ['user_id' => $provider2->id],
            [
                'bio'              => 'Professional home cleaning specialist based in Karachi. Eco-friendly products used.',
                'experience_years' => 5,
                'is_approved'      => true,
                'avg_rating'       => 4.80,
                'total_reviews'    => 20,
            ]
        );

        // ── Services ──────────────────────────────────────────────────────────

        $plumbing  = Category::where('name', 'Plumbing')->first();
        $cleaning  = Category::where('name', 'Cleaning')->first();

        if ($plumbing) {
            Service::updateOrCreate(
                ['provider_id' => $provider1->id, 'title' => 'Pipe Repair & Installation'],
                [
                    'category_id' => $plumbing->id,
                    'description' => 'Full pipe repair, replacement, and new installation services.',
                    'price'       => 500.00,
                    'price_type'  => 'fixed',
                    'is_active'   => true,
                ]
            );

            Service::updateOrCreate(
                ['provider_id' => $provider1->id, 'title' => 'Bathroom Fitting'],
                [
                    'category_id' => $plumbing->id,
                    'description' => 'Complete bathroom fitting including taps, showers, and toilets.',
                    'price'       => 1200.00,
                    'price_type'  => 'fixed',
                    'is_active'   => true,
                ]
            );
        }

        if ($cleaning) {
            Service::updateOrCreate(
                ['provider_id' => $provider2->id, 'title' => 'Deep Home Cleaning'],
                [
                    'category_id' => $cleaning->id,
                    'description' => 'Thorough deep cleaning of your entire home. Eco-friendly products.',
                    'price'       => 800.00,
                    'price_type'  => 'fixed',
                    'is_active'   => true,
                ]
            );

            Service::updateOrCreate(
                ['provider_id' => $provider2->id, 'title' => 'Office Cleaning (Hourly)'],
                [
                    'category_id' => $cleaning->id,
                    'description' => 'Professional office cleaning charged per hour.',
                    'price'       => 200.00,
                    'price_type'  => 'hourly',
                    'is_active'   => true,
                ]
            );
        }

        // ── Availability ──────────────────────────────────────────────────────

        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        foreach ($weekdays as $day) {
            Availability::updateOrCreate(
                ['provider_id' => $provider1->id, 'day_of_week' => $day],
                ['start_time' => '09:00', 'end_time' => '18:00']
            );
        }

        $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        foreach ($allDays as $day) {
            Availability::updateOrCreate(
                ['provider_id' => $provider2->id, 'day_of_week' => $day],
                ['start_time' => '08:00', 'end_time' => '20:00']
            );
        }

        // ── Customers ─────────────────────────────────────────────────────────

        User::updateOrCreate(
            ['email' => 'customer1@marketplace.com'],
            [
                'name'     => 'Bilal Ahmed',
                'password' => Hash::make('password'),
                'role'     => 'customer',
                'city'     => 'Rawalpindi',
                'phone'    => '03333333333',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer2@marketplace.com'],
            [
                'name'     => 'Sana Iqbal',
                'password' => Hash::make('password'),
                'role'     => 'customer',
                'city'     => 'Faisalabad',
                'phone'    => '03444444444',
            ]
        );
    }
}
