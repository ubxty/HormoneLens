<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change disease_type from enum to varchar to allow any disease slug
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->string('disease_type', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->enum('disease_type', ['diabetes', 'pcod'])->change();
        });
    }
};
