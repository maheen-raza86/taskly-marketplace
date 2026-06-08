<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Plumbing',    'icon' => 'wrench'],
            ['name' => 'Electrical',  'icon' => 'bolt'],
            ['name' => 'Cleaning',    'icon' => 'sparkles'],
            ['name' => 'Tutoring',    'icon' => 'academic-cap'],
            ['name' => 'Carpentry',   'icon' => 'hammer'],
            ['name' => 'Painting',    'icon' => 'paint-brush'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
