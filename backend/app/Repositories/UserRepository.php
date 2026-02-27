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

    public function paginate(int $perPage = 15, ?string $search = null, ?string $isAdmin = null)
    {
        $query = User::with('activeDigitalTwin')
            ->withCount('simulations');

        if ($isAdmin === null || $isAdmin === '') {
            // Default: show all users
        } elseif ($isAdmin === '1') {
            $query->where('is_admin', true);
        } else {
            $query->where('is_admin', false);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
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
