<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function healthProfile(): HasOne
    {
        return $this->hasOne(HealthProfile::class);
    }

    // ── Dynamic disease data ─────────────────────────

    public function diseaseData(): HasMany
    {
        return $this->hasMany(UserDiseaseData::class);
    }

    /**
     * Get disease data for a specific disease by slug.
     */
    public function diseaseDataFor(string $slug): ?UserDiseaseData
    {
        return $this->diseaseData()
            ->whereHas('disease', fn ($q) => $q->where('slug', $slug))
            ->with('disease')
            ->first();
    }

    /**
     * Get all disease data keyed by disease slug (for snapshots).
     */
    public function allDiseaseDataKeyed(): array
    {
        $this->loadMissing('diseaseData.disease');
        $result = [];
        foreach ($this->diseaseData as $data) {
            $result[$data->disease->slug] = $data->toFlatArray();
        }
        return $result;
    }

    public function digitalTwins(): HasMany
    {
        return $this->hasMany(DigitalTwin::class);
    }

    public function activeDigitalTwin(): HasOne
    {
        return $this->hasOne(DigitalTwin::class)->where('is_active', true)->latestOfMany();
    }

    public function simulations(): HasMany
    {
        return $this->hasMany(Simulation::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function ragQueryLogs(): HasMany
    {
        return $this->hasMany(RagQueryLog::class);
    }

    public function simulationResult(): HasOne
    {
        return $this->hasOne(SimulationResult::class)->latestOfMany();
    }
}
