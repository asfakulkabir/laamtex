<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $productColumns = [
            'sku' => fn (Blueprint $t) => $t->string('sku')->nullable()->after('slug'),
            'manage_stock' => fn (Blueprint $t) => $t->boolean('manage_stock')->default(false)->after('stock_quantity'),
            'stock_status' => fn (Blueprint $t) => $t->string('stock_status')->default('instock')->after('manage_stock'),
            'status' => fn (Blueprint $t) => $t->string('status')->default('publish')->after('stock_status'),
            'min_price' => fn (Blueprint $t) => $t->decimal('min_price', 10, 2)->nullable()->after('sale_price'),
            'max_price' => fn (Blueprint $t) => $t->decimal('max_price', 10, 2)->nullable()->after('min_price'),
        ];

        foreach ($productColumns as $column => $add) {
            if (!Schema::hasColumn('products', $column)) {
                Schema::table('products', function (Blueprint $table) use ($add) {
                    $add($table);
                });
            }
        }

        $variationColumns = [
            'sku' => fn (Blueprint $t) => $t->string('sku')->nullable()->after('product_id'),
            'combo_key' => fn (Blueprint $t) => $t->string('combo_key')->nullable()->after('sku'),
            'status' => fn (Blueprint $t) => $t->string('status')->default('publish')->after('combo_key'),
            'description' => fn (Blueprint $t) => $t->text('description')->nullable()->after('status'),
            'regular_price' => fn (Blueprint $t) => $t->decimal('regular_price', 10, 2)->nullable()->after('description'),
            'sale_price' => fn (Blueprint $t) => $t->decimal('sale_price', 10, 2)->nullable()->after('regular_price'),
            'manage_stock' => fn (Blueprint $t) => $t->boolean('manage_stock')->default(true)->after('sale_price'),
            'stock_quantity' => fn (Blueprint $t) => $t->integer('stock_quantity')->nullable()->after('manage_stock'),
            'stock_status' => fn (Blueprint $t) => $t->string('stock_status')->default('instock')->after('stock_quantity'),
            'image' => fn (Blueprint $t) => $t->string('image')->nullable()->after('stock_status'),
            'weight_value' => fn (Blueprint $t) => $t->decimal('weight_value', 10, 3)->nullable()->after('image'),
            'menu_order' => fn (Blueprint $t) => $t->integer('menu_order')->default(0)->after('weight_value'),
        ];

        foreach ($variationColumns as $column => $add) {
            if (!Schema::hasColumn('product_variations', $column)) {
                Schema::table('product_variations', function (Blueprint $table) use ($add) {
                    $add($table);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            foreach (['sku', 'combo_key', 'status', 'description', 'regular_price', 'sale_price', 'manage_stock', 'stock_quantity', 'stock_status', 'image', 'weight_value', 'menu_order'] as $column) {
                if (Schema::hasColumn('product_variations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            foreach (['sku', 'manage_stock', 'stock_status', 'status', 'min_price', 'max_price'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
