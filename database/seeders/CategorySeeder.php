<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Thuc an cho cho',
                'description' => 'Danh muc thuc an va dinh duong cho cho cung.',
                'image' => null,
            ],
            [
                'name' => 'Thuc an cho meo',
                'description' => 'Danh muc thuc an va dinh duong cho meo cung.',
                'image' => null,
            ],
            [
                'name' => 'Phu kien thu cung',
                'description' => 'Do choi, day dan, o nam va cac phu kien can thiet.',
                'image' => null,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'image' => $category['image'],
                ]
            );
        }
    }
}
