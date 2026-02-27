<?php

namespace App\Repositories;

use App\Models\DiseaseDiabetes;
use App\Models\User;

class DiabetesRepository
{
    public function findByUser(User $user): ?DiseaseDiabetes
    {
        return $user->diseaseDiabetes;
    }

    public function createOrUpdate(User $user, array $data): DiseaseDiabetes
    {
        return DiseaseDiabetes::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }
}
