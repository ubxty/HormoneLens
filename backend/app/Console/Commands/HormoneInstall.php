<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class HormoneInstall extends Command
{
    protected $signature = 'hormone:install
                            {--fresh : Wipe the database before installing}
                            {--seed-only : Skip migrations, only seed data}';

    protected $description = 'Set up HormoneLens for a full demo — migrations, users, diseases, RAG data & sample records';

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('🔬 HormoneLens Installer');
        $this->line('  <fg=gray>Setting up the metabolic health simulation platform…</>');
        $this->newLine();

        $startTime = microtime(true);

        // ── Step 1: Database ─────────────────────────
        if (! $this->option('seed-only')) {
            $this->runStep('Running migrations', function () {
                $cmd = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
                Artisan::call($cmd, ['--force' => true]);
            });
        }

        // ── Step 2: Core seeders ─────────────────────
        $this->runStep('Seeding admin user', fn () => Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\AdminUserSeeder', '--force' => true,
        ]));

        $this->runStep('Seeding disease definitions (4 diseases, 43 fields)', fn () => Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\DiseaseSeeder', '--force' => true,
        ]));

        $this->runStep('Seeding RAG knowledge base — Diabetes', fn () => Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\DiabetesRagSeeder', '--force' => true,
        ]));

        $this->runStep('Seeding RAG knowledge base — PCOD', fn () => Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PcodRagSeeder', '--force' => true,
        ]));

        $this->runStep('Seeding RAG knowledge base — Lifestyle & Nutrition', fn () => Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\LifestyleNutritionRagSeeder', '--force' => true,
        ]));

        // ── Step 3: Demo data ────────────────────────
        $this->runStep('Creating demo users with full health data', fn () => Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\DemoDataSeeder', '--force' => true,
        ]));

        // ── Done ─────────────────────────────────────
        $elapsed = round(microtime(true) - $startTime, 2);
        $this->newLine();
        $this->components->info("✅ HormoneLens installed successfully in {$elapsed}s");
        $this->newLine();

        $this->table(
            ['Account', 'Email', 'Password', 'Role'],
            [
                ['Admin',        'admin@hormonelens.com', 'password',    'Super Admin'],
                ['Demo – Priya', 'priya@demo.com',       'password123', 'User (Diabetes + Thyroid)'],
                ['Demo – Anita', 'anita@demo.com',       'password123', 'User (PCOD)'],
                ['Demo – Rahul', 'rahul@demo.com',       'password123', 'User (Metabolic Syndrome)'],
            ]
        );

        $this->newLine();
        $this->line('  <fg=cyan>▸</> Serve: <fg=white>php artisan serve --port=8080</>');
        $this->line('  <fg=cyan>▸</> Visit: <fg=white>http://localhost:8080</>');
        $this->newLine();

        return self::SUCCESS;
    }

    private function runStep(string $label, callable $callback): void
    {
        $this->components->task($label, function () use ($callback) {
            $callback();
            return true;
        });
    }
}
