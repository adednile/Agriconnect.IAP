<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bid;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::available()
            ->with('farmer')
            ->latest()
            ->filter(request(['search', 'category']))
            ->paginate(12);

        $categories = Product::distinct()->pluck('category');

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load('farmer');
        
        // Get similar products
        $similarProducts = Product::available()
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->with('farmer')
            ->take(4)
            ->get();

        // Get bids for this product (if user is the farmer)
        $bids = [];
        if (auth()->check() && auth()->id() === $product->farmer_id) {
            $bids = Bid::where('product_id', $product->id)
                ->with('buyer')
                ->latest()
                ->get();
        }

        return view('products.show', compact('product', 'similarProducts', 'bids'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        $products = Product::available()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($category, function ($query, $category) {
                return $query->where('category', $category);
            })
            ->with('farmer')
            ->latest()
            ->paginate(12);

        $categories = Product::distinct()->pluck('category');

        return view('products.index', compact('products', 'categories', 'search', 'category'));
    }

    public function byCategory($category)
    {
        $products = Product::available()
            ->where('category', $category)
            ->with('farmer')
            ->latest()
            ->paginate(12);

        $categories = Product::distinct()->pluck('category');

        return view('products.index', compact('products', 'categories', 'category'));
    }
}