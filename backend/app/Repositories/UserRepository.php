<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function paginate(int $perPage = 15)
    {
        return User::with('activeDigitalTwin')
            ->withCount('simulations')
            ->where('is_admin', false)
            ->latest()
            ->paginate($perPage);
    }

    public function totalCount(): int
    {
        return User::where('is_admin', false)->count();
    }

    public function newUsersCount(int $days = 7): int
    {
        return User::where('is_admin', false)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }
}
