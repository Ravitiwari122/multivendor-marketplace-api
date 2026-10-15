<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {

        $categories = Category::where('status', true)->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Categories List',
            'data' => $categories
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category Created Successfully',
            'data' => $category
        ], 201);
    }


    public function destroy(int $id)
    {
        $category = Category::findorfail($id);

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category Deleted Successfully',
        ]);
    }
}
