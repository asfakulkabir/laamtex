<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Every attribute value actually used by a variation is also an option
        // offered for that attribute on the product.
        $used = DB::table('variation_attribute_values')
            ->whereNotNull('attribute_value_id')
            ->select('product_attribute_id', 'attribute_value_id')
            ->distinct()
            ->get();

        foreach ($used as $row) {
            $exists = DB::table('product_attribute_values')
                ->where('product_attribute_id', $row->product_attribute_id)
                ->where('attribute_value_id', $row->attribute_value_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('product_attribute_values')->insert([
                'product_attribute_id' => $row->product_attribute_id,
                'attribute_value_id' => $row->attribute_value_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('product_attribute_values')->delete();
    }
};
