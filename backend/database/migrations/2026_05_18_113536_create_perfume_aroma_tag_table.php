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
        Schema::create('perfume_aroma_tag', function (Blueprint $table) {
            $table->foreignId('perfume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('aroma_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['perfume_id', 'aroma_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfume_aroma_tag');
    }
};
