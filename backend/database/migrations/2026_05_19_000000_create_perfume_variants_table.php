<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfume_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perfume_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('volume_ml')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->timestamps();

            $table->index('perfume_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfume_variants');
    }
};
