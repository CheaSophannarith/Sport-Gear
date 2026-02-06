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
        Schema::create('category_variant_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('size_value', 50); // e.g., "39", "XS", "M"
            $table->string('display_label', 100); // e.g., "EU 39", "Extra Small"
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure unique size per category
            $table->unique(['category_id', 'size_value'], 'unique_category_size');

            // Index for queries
            $table->index(['category_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_variant_sizes');
    }
};
