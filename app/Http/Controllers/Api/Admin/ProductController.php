<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreProductRequest;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Products List',
            'data' => $products
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product Created Successfully',
            'data' => $product
        ], 201);
    }

    public function update(StoreProductRequest $request, int $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product Updated Successfully',
            'data' => $product->fresh()->load('category'),
        ]);
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product Deleted Successfully',
        ]);
    }

    public function updateStatus(int $id)
    {

        $product = Product::findOrFail($id);
        $product->update(['status' => !$product->status]);
        return response()->json([
            'success' => true,
            'message' => 'Product Status Updated Successfully',
            'data' => [
                'id' => $product->id,
                'status' => $product->status,
            ],
        ]);
    }
}
