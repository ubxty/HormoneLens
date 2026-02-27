<?php

namespace App\Repositories;

use App\Models\HealthProfile;
use App\Models\User;

class HealthProfileRepository
{
    public function findByUser(User $user): ?HealthProfile
    {
        return $user->healthProfile;
    }

    public function create(array $data): HealthProfile
    {
        return HealthProfile::create($data);
    }

    public function update(HealthProfile $profile, array $data): HealthProfile
    {
        $profile->update($data);
        return $profile->fresh();
    }
}
