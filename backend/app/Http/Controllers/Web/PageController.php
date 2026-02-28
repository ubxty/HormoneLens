<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SimulationResult;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $user->load(['healthProfile', 'simulationResult']);

        $sim = $user->simulationResult;

        // Calculate age from health profile or default
        $age = null;
        if ($user->healthProfile && $user->healthProfile->created_at) {
            // If no DOB, estimate based on profile — show a default for display
            $age = $user->healthProfile->weight ? round($user->healthProfile->weight * 0.4 + 5) : null;
        }

        // Compute overall health risk score from simulation
        $riskScore = 0;
        if ($sim) {
            $riskScore = round(
                ($sim->pcos_risk + $sim->diabetes_risk + $sim->insulin_resistance_risk) / 3,
                1
            );
        }

        // Lifestyle data for chart (from health profile)
        $hp = $user->healthProfile;
        $lifestyleData = [
            'sleep'    => $hp ? (float) $hp->avg_sleep_hours : 0,
            'stress'   => $hp ? $this->stressToNumber($hp->stress_level?->value ?? 'moderate') : 5,
            'activity' => $hp ? $this->activityToNumber($hp->physical_activity?->value ?? 'moderate') : 5,
            'diet'     => $sim ? round((float) $sim->diet_score / 10, 1) : 5,
        ];

        return view('dashboard', compact('user', 'sim', 'age', 'riskScore', 'lifestyleData'));
    }

    private function stressToNumber(string $level): int
    {
        return match ($level) {
            'low'      => 3,
            'moderate' => 5,
            'high'     => 7,
            'severe'   => 9,
            default    => 5,
        };
    }

    private function activityToNumber(string $level): int
    {
        return match ($level) {
            'sedentary' => 2,
            'light'     => 4,
            'moderate'  => 6,
            'active'    => 8,
            'very_active', 'intense' => 9,
            default     => 5,
        };
    }

    public function healthProfile()   { return view('health-profile'); }
    public function disease(string $slug)
    {
        $disease = \App\Models\Disease::where('slug', $slug)->where('is_active', true)->with('fields')->firstOrFail();
        return view('disease.show', compact('disease'));
    }
    public function digitalTwin()     { return view('digital-twin'); }
    public function simulations()     { return view('simulations'); }
    public function foodImpact()      { return view('food-impact'); }
    public function alerts()          { return view('alerts'); }
    public function history()         { return view('history'); }
    public function ragQuery()        { return view('rag-query'); }
}
