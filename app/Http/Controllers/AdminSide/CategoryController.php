<?php

namespace App\Http\Controllers\AdminSide;

use App\Http\Controllers\Controller;

use App\Models\Category;

class CategoryController extends Controller
{
    public function getCategoryOption()
    {
        // Fetch categories with subcategories
        $categories = Category::with('subCategories:id,category_id,name')
            ->select('id', 'name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'subcategories' => $category->subCategories->map(function ($subcategory) {
                        return [
                            'id' => $subcategory->id,
                            'name' => $subcategory->name,
                        ];
                    }),
                ];
            });

        // Return JSON response
        return response()->json(['data' => $categories]);
    }
}
