<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rag_query_logs', function (Blueprint $table) {
            $table->string('model_used', 100)->nullable()->after('confidence');
            $table->unsignedInteger('tokens_used')->nullable()->after('model_used');
            $table->decimal('ai_cost', 8, 6)->nullable()->after('tokens_used');
            $table->unsignedInteger('latency_ms')->nullable()->after('ai_cost');
        });
    }

    public function down(): void
    {
        Schema::table('rag_query_logs', function (Blueprint $table) {
            $table->dropColumn(['model_used', 'tokens_used', 'ai_cost', 'latency_ms']);
        });
    }
};
