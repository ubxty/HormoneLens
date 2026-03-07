<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_glycemic_data', function (Blueprint $table) {
            $table->id();
            $table->string('food_item')->unique();
            $table->string('category');           // grain, sweet, vegetable, snack, beverage, dairy, fruit, legume, bread
            $table->unsignedSmallInteger('glycemic_index');       // 0–100
            $table->unsignedSmallInteger('glycemic_load');        // 0–50+
            $table->unsignedSmallInteger('typical_spike_mg_dl');  // expected blood-sugar rise
            $table->unsignedSmallInteger('peak_time_minutes');    // time to glucose peak
            $table->unsignedSmallInteger('recovery_time_minutes');// time to return near baseline
            $table->string('serving_size')->default('1 serving'); // reference portion
            $table->json('alternatives')->nullable();             // JSON array of healthier swaps
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_glycemic_data');
    }
};
