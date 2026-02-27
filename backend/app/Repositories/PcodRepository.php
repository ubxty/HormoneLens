<?php

namespace App\Repositories;

use App\Models\DiseasePcod;
use App\Models\User;

class PcodRepository
{
    public function findByUser(User $user): ?DiseasePcod
    {
        return $user->diseasePcod;
    }

    public function createOrUpdate(User $user, array $data): DiseasePcod
    {
        return DiseasePcod::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }
}
