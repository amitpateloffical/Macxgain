<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sub_categories')->truncate();
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            'Fashion' => [
                'Men\'s Clothing' => ['T-Shirts', 'Shirts', 'Jeans', 'Jackets'],
                'Women\'s Clothing' => ['Dresses', 'Sarees', 'Tops', 'Skirts'],
                'Footwear' => ['Men\'s Shoes', 'Women\'s Shoes', 'Kids\' Shoes'],
                'Accessories' => ['Bags', 'Belts', 'Watches'],
            ],
            'Electronics' => [
                'Mobiles' => ['Smartphones', 'Feature Phones'],
                'Laptops' => ['Gaming Laptops', 'Ultrabooks'],
                'Audio' => ['Headphones', 'Speakers'],
                'Wearables' => ['Smartwatches', 'Fitness Bands'],
            ],
            'Home & Kitchen' => [
                'Furniture' => ['Sofas', 'Beds', 'Tables'],
                'Kitchen Appliances' => ['Microwaves', 'Refrigerators'],
                'Decor' => ['Wall Art', 'Clocks', 'Lighting'],
            ],
            'Sports & Outdoors' => [
                'Fitness Equipment' => ['Treadmills', 'Dumbbells'],
                'Outdoor Gear' => ['Tents', 'Sleeping Bags'],
                'Apparel' => ['Sportswear', 'Running Shoes'],
            ],
            'Books & Stationery' => [
                'Books' => ['Fiction', 'Non-Fiction', 'Children\'s Books'],
                'Stationery' => ['Notebooks', 'Pens', 'Art Supplies'],
            ],
        ];

        foreach ($categories as $category => $subcategories) {
            $categorySlug = Str::slug($category);
            $originalCategorySlug = $categorySlug;
            $categoryCount = 1;

            while (DB::table('categories')->where('slug', $categorySlug)->exists()) {
                $categorySlug = $originalCategorySlug . '-' . $categoryCount;
                $categoryCount++;
            }

            $categoryId = DB::table('categories')->insertGetId([
                'name' => $category,
                'slug' => $categorySlug,
            ]);

            foreach ($subcategories as $subcategory => $childSubcategories) {
                $subSlug = Str::slug($subcategory);
                $originalSubSlug = $subSlug;
                $subCount = 1;

                while (DB::table('sub_categories')->where('slug', $subSlug)->exists()) {
                    $subSlug = $originalSubSlug . '-' . $subCount;
                    $subCount++;
                }

                $subcategoryId = DB::table('sub_categories')->insertGetId([
                    'category_id' => $categoryId,
                    'name' => $subcategory,
                    'slug' => $subSlug,
                ]);

                foreach ($childSubcategories as $childSubcategory) {
                    $childSubSlug = Str::slug($childSubcategory);
                    $originalChildSubSlug = $childSubSlug;
                    $childSubCount = 1;

                    while (DB::table('sub_categories')->where('slug', $childSubSlug)->exists()) {
                        $childSubSlug = $originalChildSubSlug . '-' . $childSubCount;
                        $childSubCount++;
                    }

                    DB::table('sub_categories')->insert([
                        'category_id' => $categoryId,
                        // 'parent_id' => $subcategoryId, // Assuming `sub_categories` has a `parent_id` column for nesting
                        'name' => $childSubcategory,
                        'slug' => $childSubSlug,
                    ]);
                }
            }
        }
    }
}
