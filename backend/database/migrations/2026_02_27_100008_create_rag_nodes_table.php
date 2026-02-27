<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rag_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('rag_documents')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('rag_nodes')->cascadeOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('keywords')->comment('comma-separated');
            $table->unsignedTinyInteger('depth')->default(0);
            $table->timestamps();

            $table->index(['document_id', 'depth']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rag_nodes');
    }
};
