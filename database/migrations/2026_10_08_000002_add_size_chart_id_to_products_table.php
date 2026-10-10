<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // A product uses at most one size chart, so this is a plain
            // nullable foreign key rather than a pivot table. Null means the
            // product simply has no size chart link on its page.
            $table->foreignId('size_chart_id')
                ->nullable()
                ->after('is_featured')
                ->constrained('size_charts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('size_chart_id');
        });
    }
};
