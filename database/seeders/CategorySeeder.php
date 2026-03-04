<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'Buy & Sell' => ['Electronics', 'Furniture', 'Clothing', 'Home Goods'],
            'Services' => ['Home Services', 'Beauty', 'Lessons', 'Repairs'],
            'Housing' => ['Rentals', 'Roommates', 'Short-Term', 'For Sale'],
            'Jobs' => ['Full-time', 'Part-time', 'Contract', 'Gig Work'],
            'Vehicles' => ['Cars', 'Trucks', 'Motorcycles', 'Parts'],
            'Community' => ['Announcements', 'Lost & Found', 'Volunteering'],
            'Local Businesses' => ['Promotions', 'Openings', 'Collaborations'],
            'Events' => ['Music', 'Sports', 'Family', 'Meetups'],
            'Discussions' => ['General', 'Advice', 'Recommendations'],
        ];

        $parentOrder = 0;
        foreach ($structure as $parentName => $children) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'parent_id' => null,
                    'sort_order' => $parentOrder++,
                    'is_active' => true,
                ]
            );

            $childOrder = 0;
            foreach ($children as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($parentName . ' ' . $childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'sort_order' => $childOrder++,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
