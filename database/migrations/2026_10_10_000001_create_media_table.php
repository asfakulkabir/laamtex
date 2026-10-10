<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One central library for every image, video and audio file the shop uses.
 *
 * Admin screens pick from here instead of only accepting a fresh upload, so
 * the same asset can be reused by a slider, a product or a variation without
 * storing another copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Path on the storage disk, which is also what the existing
            // per-feature tables store, so a picked file can be referenced
            // without copying it.
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('kind', 16); // image | video | audio
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            // Lets the same file be recognised when it is uploaded twice.
            $table->string('hash', 64)->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['kind', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};