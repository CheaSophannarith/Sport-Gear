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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('featured_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->bigInteger('view_count')->default(0);

            // Filter relationships (one-to-many)
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('league_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('surface_type_id')->nullable()->constrained('surface_types')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index('slug');
            $table->index('category_id');
            $table->index(['is_featured', 'is_active']);
            $table->index('view_count');
            $table->index('brand_id');
            $table->index('league_id');
            $table->index('team_id');
            $table->index('surface_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
