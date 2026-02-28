<?php

namespace Database\Seeders;

use App\Models\SimulationResult;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimulationResultSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Aisha Patel', 'Rohan Mehta', 'Sneha Kapoor', 'Vikram Nair',
            'Divya Reddy', 'Arjun Sharma', 'Meera Iyer', 'Kabir Singh',
            'Tanvi Gupta', 'Nikhil Joshi',
        ];

        foreach ($names as $i => $name) {
            $user = User::updateOrCreate(
                ['email' => 'simuser' . ($i + 1) . '@demo.com'],
                [
                    'name'     => $name,
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                ]
            );

            SimulationResult::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'metabolic_score'         => $this->rand(40, 90),
                    'insulin_score'           => $this->rand(40, 90),
                    'sleep_score'             => $this->rand(40, 90),
                    'stress_score'            => $this->rand(40, 90),
                    'diet_score'              => $this->rand(40, 90),
                    'pcos_risk'               => $this->rand(20, 80),
                    'diabetes_risk'           => $this->rand(20, 80),
                    'insulin_resistance_risk' => $this->rand(20, 80),
                ]
            );
        }

        // Also seed simulation results for existing demo users
        $demoEmails = ['priya@demo.com', 'anita@demo.com', 'rahul@demo.com'];
        foreach ($demoEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                SimulationResult::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'metabolic_score'         => $this->rand(40, 90),
                        'insulin_score'           => $this->rand(40, 90),
                        'sleep_score'             => $this->rand(40, 90),
                        'stress_score'            => $this->rand(40, 90),
                        'diet_score'              => $this->rand(40, 90),
                        'pcos_risk'               => $this->rand(20, 80),
                        'diabetes_risk'           => $this->rand(20, 80),
                        'insulin_resistance_risk' => $this->rand(20, 80),
                    ]
                );
            }
        }

        // Seed for admin user too
        $admin = User::where('is_admin', true)->first();
        if ($admin) {
            SimulationResult::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'metabolic_score'         => $this->rand(55, 85),
                    'insulin_score'           => $this->rand(55, 85),
                    'sleep_score'             => $this->rand(55, 85),
                    'stress_score'            => $this->rand(55, 85),
                    'diet_score'              => $this->rand(55, 85),
                    'pcos_risk'               => $this->rand(20, 60),
                    'diabetes_risk'           => $this->rand(20, 60),
                    'insulin_resistance_risk' => $this->rand(20, 60),
                ]
            );
        }
    }

    private function rand(int $min, int $max): float
    {
        return round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), 2);
    }
}
