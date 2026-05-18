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
        Schema::create('perfumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('official_description')->nullable();
            $table->string('concentration')->nullable();
            $table->unsignedSmallInteger('volume_ml')->nullable();
            $table->unsignedInteger('price_min')->nullable();
            $table->unsignedInteger('price_max')->nullable();
            $table->string('image_url')->nullable();
            $table->string('marketed_gender')->nullable();
            $table->string('intensity')->nullable();
            $table->foreignId('main_aroma_category_id')->nullable()->constrained('aroma_categories')->nullOnDelete();
            $table->string('source_url')->nullable();
            $table->string('source_name')->nullable();
            $table->date('last_verified_at')->nullable();
            $table->string('data_status')->default('draft');
            $table->timestamps();

            $table->index(['brand_id', 'data_status']);
            $table->index('main_aroma_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfumes');
    }
};
