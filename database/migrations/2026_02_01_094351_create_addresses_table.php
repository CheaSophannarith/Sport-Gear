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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('province_id')->constrained();
            $table->string('label');
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('street_address')->nullable();
            $table->string('village');
            $table->string('district');
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('province_id');
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
