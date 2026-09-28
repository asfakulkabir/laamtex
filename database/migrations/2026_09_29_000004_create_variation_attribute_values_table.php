<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variation_id')->constrained('product_variations')->cascadeOnDelete();
            $table->foreignId('product_attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->nullable()->constrained('attribute_values')->nullOnDelete();
            $table->string('custom_value')->nullable();
            $table->timestamps();

            // One value per attribute per variation.
            $table->unique(['product_variation_id', 'product_attribute_id'], 'vav_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variation_attribute_values');
    }
};
