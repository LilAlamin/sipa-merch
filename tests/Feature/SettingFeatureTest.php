<?php

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ProductSeeder::class);
    Storage::fake('public');
});

test('settings page loads successfully with stock stats and product list', function () {
    $response = $this->get(route('pos.settings'));

    $response->assertOk();
    $response->assertViewIs('pos.settings');
    $response->assertViewHas('products', function ($products) {
        return $products->count() === 9;
    });
    $response->assertSee('Pengaturan');
    $response->assertSee('Stok Merchandise');
    $response->assertSee('Kaos');
    $response->assertSee('Ganci');
});

test('cashier or admin can update product stock and details', function () {
    $product = Product::where('slug', 'kaos')->firstOrFail();

    $response = $this->put(route('pos.settings.products.update', $product), [
        'name' => 'Kaos Festival SIPA 2026',
        'stock' => 200,
        'cost_price' => 80000,
        'selling_price' => 125000,
        'badge_text' => 'Official Festival Tee',
        'description' => 'Bahan combed 24s sablon discharge tahan lama.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $product->refresh();
    expect($product->stock)->toBe(200);
    expect($product->name)->toBe('Kaos Festival SIPA 2026');
    expect($product->selling_price)->toBe(125000);
    expect($product->cost_price)->toBe(80000);
    expect($product->badge_text)->toBe('Official Festival Tee');
});

test('cashier or admin can upload a merchandise photo', function () {
    $product = Product::where('slug', 'kaos')->firstOrFail();
    $file = UploadedFile::fake()->image('kaos_sipa.webp', 600, 600);

    $response = $this->put(route('pos.settings.products.update', $product), [
        'name' => $product->name,
        'stock' => $product->stock,
        'cost_price' => $product->cost_price,
        'selling_price' => $product->selling_price,
        'image' => $file,
    ]);

    $response->assertRedirect();
    $product->refresh();

    expect($product->image)->not->toBeNull();
    Storage::disk('public')->assertExists($product->image);
    expect($product->image_url)->toContain('storage/'.$product->image);
});

test('can create a new merchandise product from settings', function () {
    $file = UploadedFile::fake()->image('totebag.png', 500, 500);

    $response = $this->post(route('pos.settings.products.store'), [
        'name' => 'Tote Bag SIPA Official 2026',
        'category' => 'Aksesoris',
        'type' => 'satuan',
        'stock' => 75,
        'cost_price' => 35000,
        'selling_price' => 65000,
        'badge_text' => 'New Item',
        'description' => 'Kanvas tebal dengan ritsleting premium',
        'image' => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('products', [
        'name' => 'Tote Bag SIPA Official 2026',
        'category' => 'Aksesoris',
        'stock' => 75,
        'cost_price' => 35000,
        'selling_price' => 65000,
    ]);

    $newProduct = Product::where('name', 'Tote Bag SIPA Official 2026')->firstOrFail();
    expect($newProduct->image)->not->toBeNull();
    Storage::disk('public')->assertExists($newProduct->image);
});
