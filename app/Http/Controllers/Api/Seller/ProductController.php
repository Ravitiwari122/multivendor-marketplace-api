<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where(
            'seller_id',
            $request->user()->id
        )->with('category')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Products List',
            'data' => $products
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['seller_id'] = $request->user()->id;
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
        $product = Product::where('seller_id', $request->user()->id)
        ->where('id', $id)
        ->firstOrFail();
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

    public function destroy(Request $request, int $id)
    {
        $product = Product::where('seller_id', $request->user()->id)
        ->where('id',$id)
        ->firstOrFail();
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product Deleted Successfully',
        ]);
    }
}
