<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('menu')->unique();
            $table->decimal('energy_kj', 10, 2)->nullable();
            $table->decimal('protein_g', 10, 2)->nullable();
            $table->decimal('fat_g', 10, 2)->nullable();
            $table->decimal('carbohydrates_g', 10, 2)->nullable();
            $table->decimal('dietary_fiber_g', 10, 2)->nullable();
            $table->decimal('pufa_g', 10, 2)->nullable();
            $table->decimal('cholesterol_mg', 10, 2)->nullable();
            $table->decimal('vitamin_a_mg', 10, 2)->nullable();
            $table->decimal('vitamin_e_mg', 10, 2)->nullable();
            $table->decimal('vitamin_b1_mg', 10, 2)->nullable();
            $table->decimal('vitamin_b2_mg', 10, 2)->nullable();
            $table->decimal('vitamin_b6_mg', 10, 2)->nullable();
            $table->decimal('total_folic_acid_ug', 10, 2)->nullable();
            $table->decimal('vitamin_c_mg', 10, 2)->nullable();
            $table->decimal('sodium_mg', 10, 2)->nullable();
            $table->decimal('potassium_mg', 10, 2)->nullable();
            $table->decimal('calcium_mg', 10, 2)->nullable();
            $table->decimal('magnesium_mg', 10, 2)->nullable();
            $table->decimal('phosphorus_mg', 10, 2)->nullable();
            $table->decimal('iron_mg', 10, 2)->nullable();
            $table->decimal('zinc_mg', 10, 2)->nullable();
            $table->timestamps();

            $table->index('menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
