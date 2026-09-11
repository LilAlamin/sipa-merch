<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('satuan')->index(); // satuan, bundling, limited
            $table->string('category')->default('Merchandise')->index();
            $table->unsignedInteger('cost_price')->default(0); // HPP
            $table->unsignedInteger('selling_price')->default(0); // Harga Jual
            $table->integer('stock')->default(100);
            $table->boolean('has_variants')->default(false);
            $table->json('variants')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->string('channel')->default('ots')->index(); // ots, po
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_notes')->nullable();
            $table->unsignedInteger('total_cost')->default(0);
            $table->unsignedInteger('total_price')->default(0);
            $table->integer('profit')->default(0);
            $table->string('payment_method')->default('cash'); // cash, qris, transfer
            $table->string('payment_status')->default('paid')->index(); // paid, dp, unpaid
            $table->unsignedInteger('amount_paid')->default(0);
            $table->integer('change_amount')->default(0);
            $table->string('order_status')->default('completed')->index(); // completed, pending_pickup, cancelled
            $table->date('pickup_date')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('cost_price')->default(0);
            $table->unsignedInteger('unit_price')->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('subtotal_cost')->default(0);
            $table->string('variant')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
