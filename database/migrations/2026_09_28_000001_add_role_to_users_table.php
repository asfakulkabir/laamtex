<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('customer')->after('is_admin')->index();
            });
        }

        // Backfill: every existing admin becomes a super admin, everyone else a customer.
        DB::table('users')->where('is_admin', true)->update(['role' => 'super_admin']);
        DB::table('users')->where('is_admin', false)->update(['role' => 'customer']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
