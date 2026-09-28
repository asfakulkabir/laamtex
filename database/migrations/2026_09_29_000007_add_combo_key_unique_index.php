<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy rows that were never converted (no size/color) would all share
        // an empty combo key, so they get a stable unique placeholder first.
        DB::table('product_variations')
            ->whereNull('combo_key')
            ->orWhere('combo_key', '')
            ->orderBy('id')
            ->each(function ($variation) {
                $hasValues = DB::table('variation_attribute_values')
                    ->where('product_variation_id', $variation->id)
                    ->exists();

                if ($hasValues) {
                    return;
                }

                DB::table('product_variations')
                    ->where('id', $variation->id)
                    ->update(['combo_key' => 'legacy-' . $variation->id]);
            });

        $duplicates = DB::table('product_variations')
            ->select('product_id', 'combo_key', DB::raw('COUNT(*) as total'))
            ->whereNotNull('combo_key')
            ->groupBy('product_id', 'combo_key')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $dupe) {
            $ids = DB::table('product_variations')
                ->where('product_id', $dupe->product_id)
                ->where('combo_key', $dupe->combo_key)
                ->orderBy('id')
                ->pluck('id');

            $ids->slice(1)->each(function ($id) {
                DB::table('product_variations')->where('id', $id)->update(['combo_key' => 'dup-' . $id]);
            });
        }

        if (!Schema::hasTable('product_variations')) {
            return;
        }

        if (! $this->indexExists()) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->unique(['product_id', 'combo_key'], 'variations_combo_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists()) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->dropUnique('variations_combo_unique');
            });
        }
    }

    private function indexExists(): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            foreach (DB::select("PRAGMA index_list('product_variations')") as $index) {
                if ($index->name === 'variations_combo_unique') {
                    return true;
                }
            }

            return false;
        }

        return collect(DB::select('SHOW INDEX FROM product_variations WHERE Key_name = ?', ['variations_combo_unique']))->isNotEmpty();
    }
};
