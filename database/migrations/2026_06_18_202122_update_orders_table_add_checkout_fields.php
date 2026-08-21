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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Drop old columns that we're replacing
            $table->dropColumn([
                'user_id',
                'customer_email',
                'shipping_zone',
                'shipping_charge',
                'subtotal',
                'notes',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add new columns to match the desired model
            $table->longText('items_json')->default('')->after('id');
            $table->string('payment_method')->default('Cash on Delivery')->after('items_json');
            $table->unsignedBigInteger('delivery_charge_id')->nullable()->after('payment_method');
            $table->string('bkash_trx_id')->nullable()->after('delivery_charge_id');
            $table->integer('total_amount')->default(0)->after('bkash_trx_id');
            $table->boolean('is_sent_to_steadfast')->default(false)->after('total_amount');
            $table->boolean('is_notification_sent')->default(false)->after('is_sent_to_steadfast');
            $table->string('steadfast_consignment_id')->nullable()->after('is_notification_sent');

            // Add foreign key for delivery_charge_id
            $table->foreign('delivery_charge_id')->references('id')->on('delivery_charges')->onDelete('set null');
        });

        // Rename shipping_address to customer_address if it exists
        if (Schema::hasColumn('orders', 'shipping_address')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('shipping_address', 'customer_address');
            });
        }

        // Update status column with new enum values
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('processing')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_charge_id']);
            $table->dropColumn([
                'items_json',
                'payment_method',
                'delivery_charge_id',
                'bkash_trx_id',
                'total_amount',
                'is_sent_to_steadfast',
                'is_notification_sent',
                'steadfast_consignment_id',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_email')->nullable();
            $table->string('shipping_zone')->nullable();
            $table->decimal('shipping_charge', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('pending')->change();
        });

        if (Schema::hasColumn('orders', 'customer_address')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('customer_address', 'shipping_address');
            });
        }
    }
};
