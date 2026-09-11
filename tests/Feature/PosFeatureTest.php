<?php

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ProductSeeder::class);
});

test('pos terminal page loads successfully with seeded products', function () {
    $response = $this->get(route('pos.index'));

    $response->assertOk();
    $response->assertViewHas('products', function ($products) {
        return $products->count() === 9;
    });
    $response->assertSee('Kaos');
    $response->assertSee('Ganci');
    $response->assertSee('Bundling pin');
    $response->assertSee('Stiker limited');
});

test('checkout processes OTS order successfully and calculates profit and change accurately', function () {
    $kaos = Product::where('slug', 'kaos')->firstOrFail();
    $pin = Product::where('slug', 'pin')->firstOrFail();

    $payload = [
        'channel' => 'ots',
        'items' => [
            ['id' => $kaos->id, 'quantity' => 1, 'variant' => 'L'],
            ['id' => $pin->id, 'quantity' => 2, 'variant' => null],
        ],
        'customer_name' => null,
        'customer_phone' => null,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'amount_paid' => 150000,
    ];

    $response = $this->postJson(route('pos.checkout'), $payload);

    $response->assertOk();
    $response->assertJsonPath('success', true);

    // Kaos: 110.000 + (Pin: 2 x 5.000 = 10.000) = 120.000
    // HPP Kaos: 75.000 + (Pin: 2 x 1.550 = 3.100) = 78.100
    // Profit: 120.000 - 78.100 = 41.900
    // Change: 150.000 - 120.000 = 30.000
    $this->assertDatabaseHas('orders', [
        'channel' => 'ots',
        'total_price' => 120000,
        'total_cost' => 78100,
        'profit' => 41900,
        'amount_paid' => 150000,
        'change_amount' => 30000,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'order_status' => 'completed',
    ]);

    $order = Order::latest()->first();
    expect($order->items)->toHaveCount(2);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $kaos->id,
        'product_name' => 'Kaos',
        'variant' => 'L',
        'quantity' => 1,
        'subtotal' => 110000,
    ]);
});

test('checkout validates required customer name for PO orders', function () {
    $ganci = Product::where('slug', 'ganci-1')->firstOrFail();

    $payload = [
        'channel' => 'po',
        'items' => [
            ['id' => $ganci->id, 'quantity' => 1],
        ],
        'customer_name' => '', // Empty!
        'payment_method' => 'transfer',
        'payment_status' => 'dp',
        'amount_paid' => 10000,
    ];

    $response = $this->postJson(route('pos.checkout'), $payload);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
});

test('checkout processes PO order with customer info and pending pickup status', function () {
    $bundling = Product::where('slug', 'bundling-pin')->firstOrFail();

    $payload = [
        'channel' => 'po',
        'customer_name' => 'Aditya Pratama',
        'customer_phone' => '081234567890',
        'customer_notes' => 'Ambil saat hari H festival',
        'items' => [
            ['id' => $bundling->id, 'quantity' => 2],
        ],
        'payment_method' => 'qris',
        'payment_status' => 'dp',
        'amount_paid' => 12000,
        'pickup_date' => now()->addDays(3)->format('Y-m-d'),
    ];

    $response = $this->postJson(route('pos.checkout'), $payload);

    $response->assertOk();
    $response->assertJsonPath('success', true);

    $this->assertDatabaseHas('orders', [
        'channel' => 'po',
        'customer_name' => 'Aditya Pratama',
        'customer_phone' => '081234567890',
        'total_price' => 24000,
        'payment_status' => 'dp',
        'order_status' => 'pending_pickup',
    ]);
});

test('history page renders summary metrics and order list', function () {
    $kaos = Product::where('slug', 'kaos')->firstOrFail();

    Order::create([
        'order_number' => 'SIPA-OTS-260911-001',
        'channel' => 'ots',
        'customer_name' => 'Pembeli OTS',
        'total_cost' => 75000,
        'total_price' => 110000,
        'profit' => 35000,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'amount_paid' => 110000,
        'change_amount' => 0,
        'order_status' => 'completed',
    ])->items()->create([
        'product_id' => $kaos->id,
        'product_name' => 'Kaos',
        'cost_price' => 75000,
        'unit_price' => 110000,
        'quantity' => 1,
        'subtotal' => 110000,
        'subtotal_cost' => 75000,
        'variant' => 'XL',
    ]);

    $response = $this->get(route('pos.history'));

    $response->assertOk();
    $response->assertSee('SIPA-OTS-260911-001');
    $response->assertSee('Kaos');
    $response->assertSee('110.000');
});

test('order status update endpoint updates PO payment and pickup status', function () {
    $order = Order::create([
        'order_number' => 'SIPA-PO-260911-002',
        'channel' => 'po',
        'customer_name' => 'Sarah',
        'total_cost' => 5000,
        'total_price' => 12000,
        'profit' => 7000,
        'payment_method' => 'transfer',
        'payment_status' => 'dp',
        'amount_paid' => 5000,
        'change_amount' => 0,
        'order_status' => 'pending_pickup',
    ]);

    $response = $this->patchJson(route('pos.orders.status', $order), [
        'payment_status' => 'paid',
        'order_status' => 'completed',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);

    expect($order->fresh()->payment_status)->toBe('paid');
    expect($order->fresh()->order_status)->toBe('completed');
});

test('invoice view displays order slip with itemized breakdown', function () {
    $ganci = Product::where('slug', 'ganci-2')->firstOrFail();

    $order = Order::create([
        'order_number' => 'SIPA-OTS-260911-003',
        'channel' => 'ots',
        'customer_name' => 'Budi',
        'customer_phone' => '08123456789',
        'total_cost' => 14000,
        'total_price' => 20000,
        'profit' => 6000,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'amount_paid' => 50000,
        'change_amount' => 30000,
        'order_status' => 'completed',
    ]);

    $order->items()->create([
        'product_id' => $ganci->id,
        'product_name' => $ganci->name,
        'cost_price' => 14000,
        'unit_price' => 20000,
        'quantity' => 1,
        'subtotal' => 20000,
        'subtotal_cost' => 14000,
    ]);

    $response = $this->get(route('pos.invoice', $order));

    $response->assertOk();
    $response->assertSee('#SIPA-OTS-260911-003');
    $response->assertSee('Ganci 2');
    $response->assertSee('20.000');
    $response->assertSee('Kembalian');
    $response->assertSee('30.000');
    $response->assertSee('Kirim Invoice via WhatsApp');
});

test('history page defaults to today and filters accurately by channel and specific dates', function () {
    $todayOrder = Order::create([
        'order_number' => 'SIPA-OTS-TODAY',
        'channel' => 'ots',
        'customer_name' => 'Doni',
        'total_cost' => 10000,
        'total_price' => 15000,
        'profit' => 5000,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'amount_paid' => 15000,
        'change_amount' => 0,
        'order_status' => 'completed',
        'created_at' => Carbon::today()->setTime(10, 0),
    ]);

    $pastOrder = Order::create([
        'order_number' => 'SIPA-PO-PAST',
        'channel' => 'po',
        'customer_name' => 'Maya',
        'total_cost' => 20000,
        'total_price' => 30000,
        'profit' => 10000,
        'payment_method' => 'qris',
        'payment_status' => 'paid',
        'amount_paid' => 30000,
        'change_amount' => 0,
        'order_status' => 'completed',
    ]);
    $pastOrder->created_at = Carbon::yesterday()->setTime(14, 0);
    $pastOrder->saveQuietly();

    // Default visit (should default to today)
    $response = $this->get(route('pos.history'));
    $response->assertOk();
    $response->assertSee('SIPA-OTS-TODAY');
    $response->assertDontSee('SIPA-PO-PAST');
    $response->assertSee('Export Excel');

    // Filter by yesterday
    $yesterdayResponse = $this->get(route('pos.history', ['date' => 'yesterday']));
    $yesterdayResponse->assertOk();
    $yesterdayResponse->assertSee('SIPA-PO-PAST');
    $yesterdayResponse->assertDontSee('SIPA-OTS-TODAY');

    // Filter by all
    $allResponse = $this->get(route('pos.history', ['date' => 'all']));
    $allResponse->assertOk();
    $allResponse->assertSee('SIPA-OTS-TODAY');
    $allResponse->assertSee('SIPA-PO-PAST');

    // Filter by specific date string
    $specificDate = Carbon::yesterday()->toDateString();
    $specificResponse = $this->get(route('pos.history', ['date' => $specificDate]));
    $specificResponse->assertOk();
    $specificResponse->assertSee('SIPA-PO-PAST');
    $specificResponse->assertDontSee('SIPA-OTS-TODAY');
});

test('pos sales report can be exported to styled excel spreadsheet', function () {
    $order = Order::create([
        'order_number' => 'SIPA-OTS-EXP-001',
        'channel' => 'ots',
        'customer_name' => 'Fani',
        'customer_phone' => '08987654321',
        'customer_notes' => 'Catatan khusus',
        'total_cost' => 75000,
        'total_price' => 110000,
        'profit' => 35000,
        'payment_method' => 'qris',
        'payment_status' => 'paid',
        'amount_paid' => 110000,
        'change_amount' => 0,
        'order_status' => 'completed',
        'created_at' => Carbon::today(),
    ]);

    $order->items()->create([
        'product_name' => 'Kaos SIPA Festival',
        'cost_price' => 75000,
        'unit_price' => 110000,
        'quantity' => 1,
        'subtotal' => 110000,
        'subtotal_cost' => 75000,
        'variant' => 'L',
    ]);

    $response = $this->get(route('pos.history.export', ['date' => 'today']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment; filename="Laporan_Penjualan_SIPA_Merch_');
    expect($response->headers->get('Content-Disposition'))->toContain('.xlsx');
});
