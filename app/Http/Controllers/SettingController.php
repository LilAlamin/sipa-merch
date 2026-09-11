<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display product and stock settings.
     */
    public function index(): View
    {
        $products = Product::orderBy('category')->orderBy('id')->get();
        $totalStock = $products->sum('stock');
        $lowStockCount = $products->where('stock', '<=', 10)->count();

        return view('pos.settings', compact('products', 'totalStock', 'lowStockCount'));
    }

    /**
     * Update product stock, image, and details.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'stock' => 'required|integer|min:0',
            'cost_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'badge_text' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'image_url' => 'nullable|url|max:500',
            'remove_image' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_image')) {
            if ($product->image && ! str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = null;
        }

        if ($request->hasFile('image')) {
            // Delete previous local file if exists
            if ($product->image && ! str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete($product->image);
            }

            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        } elseif ($request->filled('image_url')) {
            $product->image = $validated['image_url'];
        }

        $product->name = $validated['name'];
        $product->stock = $validated['stock'];
        $product->cost_price = $validated['cost_price'];
        $product->selling_price = $validated['selling_price'];
        $product->badge_text = $validated['badge_text'] ?? null;
        $product->description = $validated['description'] ?? null;
        $product->save();

        return back()->with('success', "Data produk {$product->name} berhasil diperbarui!");
    }

    /**
     * Store new product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'type' => 'required|in:satuan,bundling,limited',
            'stock' => 'required|integer|min:0',
            'cost_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'badge_text' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $slug = Str::slug($validated['name']);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::random(4);
        }

        Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'type' => $validated['type'],
            'stock' => $validated['stock'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'badge_text' => $validated['badge_text'] ?? null,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_active' => true,
        ]);

        return back()->with('success', 'Produk baru berhasil ditambahkan!');
    }
}
