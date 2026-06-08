<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@marketplace.com'],
            [
                'name'     => 'Maheen Raza',
                'email'    => 'admin@marketplace.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'city'     => 'Islamabad',
                'phone'    => '03000000000',
            ]
        );
    }
}
