<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Buy & Sell',
            'Services',
            'Housing',
            'Jobs',
            'Vehicles',
            'Community',
            'Local Businesses',
            'Events',
            'Discussions',
        ];

        foreach ($categories as $name) {
            $slug = Str::slug($name);

            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'parent_id' => null,
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
