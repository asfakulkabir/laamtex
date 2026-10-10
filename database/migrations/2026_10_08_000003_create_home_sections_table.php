<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();

            // Heading and optional subheading shown above the products. The
            // admin types these, so the category name is only a fallback.
            $table->string('title');
            $table->string('subtitle')->nullable();

            // Products come from this category, including its child
            // categories. Null means "every active product", which is useful
            // for a section that is not tied to one category.
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // slider = horizontal scrolling carousel, grid = responsive grid.
            $table->string('layout')->default('grid');

            $table->unsignedSmallInteger('product_limit')->default(8);

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
