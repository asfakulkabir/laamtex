<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'orders_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('orders_seen_at')->nullable()->after('role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'orders_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('orders_seen_at');
            });
        }
    }
};
