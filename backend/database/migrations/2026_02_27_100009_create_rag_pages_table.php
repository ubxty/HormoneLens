<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rag_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('rag_nodes')->cascadeOnDelete();
            $table->unsignedSmallInteger('page_number');
            $table->longText('content');
            $table->timestamps();

            $table->index(['node_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rag_pages');
    }
};
