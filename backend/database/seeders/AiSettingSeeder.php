<?php

namespace Database\Seeders;

use App\Models\AiSetting;
use Illuminate\Database\Seeder;

class AiSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'ai_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable AI features globally'],
            ['key' => 'default_model', 'value' => 'default', 'type' => 'string', 'group' => 'models', 'description' => 'Default model alias for AI requests'],
            ['key' => 'fast_model', 'value' => 'fast', 'type' => 'string', 'group' => 'models', 'description' => 'Fast model alias for quick tasks'],
            ['key' => 'max_tokens', 'value' => '1024', 'type' => 'integer', 'group' => 'limits', 'description' => 'Maximum tokens per AI request'],
            ['key' => 'daily_cost_limit', 'value' => '10', 'type' => 'integer', 'group' => 'limits', 'description' => 'Daily cost limit in USD'],
            ['key' => 'monthly_cost_limit', 'value' => '100', 'type' => 'integer', 'group' => 'limits', 'description' => 'Monthly cost limit in USD'],
            ['key' => 'rag_ai_synthesis', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable AI synthesis for RAG answers'],
            ['key' => 'simulation_ai_explanation', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable AI explanations for simulations'],
            ['key' => 'alert_ai_enhancement', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable AI-enhanced alert messages'],
        ];

        foreach ($settings as $setting) {
            AiSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
