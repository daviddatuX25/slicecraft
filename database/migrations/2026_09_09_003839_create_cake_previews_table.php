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
        // NOTE (change NOT in the worksheet instructions): Curated preview
        // records connect each cake base to the teammate's twenty generated
        // image files while allowing a safe local fallback before assets land.
        Schema::create('cake_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('selection_key');
            $table->string('image_path');
            $table->string('alt_text');
            $table->timestamps();

            $table->unique(['product_id', 'selection_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cake_previews');
    }
};
