<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            // What the section shows: products from a category ("category"),
            // the hand-picked featured products ("featured") or the newest
            // arrivals ("latest"). Existing rows stay category-based.
            $table->string('content_type')->default('category')->after('subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropColumn('content_type');
        });
    }
};
