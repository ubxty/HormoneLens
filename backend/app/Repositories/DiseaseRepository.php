<?php

namespace App\Repositories;

use App\Models\Disease;
use App\Models\UserDiseaseData;
use App\Models\User;

class DiseaseRepository
{
    /**
     * Get all active diseases with their fields.
     */
    public function allActive()
    {
        return Disease::active()->ordered()->with('fields')->get();
    }

    /**
     * Find a disease by slug with its fields.
     */
    public function findBySlug(string $slug): ?Disease
    {
        return Disease::where('slug', $slug)->with('fields')->first();
    }

    /**
     * Find a disease by ID with its fields.
     */
    public function findById(int $id): ?Disease
    {
        return Disease::with('fields')->find($id);
    }

    /**
     * Get user's data for a specific disease.
     */
    public function findUserData(User $user, Disease $disease): ?UserDiseaseData
    {
        return UserDiseaseData::where('user_id', $user->id)
            ->where('disease_id', $disease->id)
            ->first();
    }

    /**
     * Get all disease data for a user (keyed by disease slug).
     */
    public function allUserData(User $user)
    {
        return UserDiseaseData::where('user_id', $user->id)
            ->with('disease')
            ->get();
    }

    /**
     * Create or update user's disease data.
     */
    public function createOrUpdate(User $user, Disease $disease, array $fieldValues): UserDiseaseData
    {
        return UserDiseaseData::updateOrCreate(
            ['user_id' => $user->id, 'disease_id' => $disease->id],
            ['field_values' => $fieldValues],
        );
    }

    /**
     * Delete user's disease data.
     */
    public function deleteUserData(User $user, Disease $disease): bool
    {
        return UserDiseaseData::where('user_id', $user->id)
            ->where('disease_id', $disease->id)
            ->delete() > 0;
    }

    // ── Admin methods ────────────────────────────────

    /**
     * Create a new disease.
     */
    public function createDisease(array $data): Disease
    {
        return Disease::create($data);
    }

    /**
     * Update a disease.
     */
    public function updateDisease(Disease $disease, array $data): Disease
    {
        $disease->update($data);
        return $disease->fresh();
    }

    /**
     * Paginate all diseases for admin.
     */
    public function paginateAll(int $perPage = 20)
    {
        return Disease::withCount(['fields', 'userData'])
            ->ordered()
            ->paginate($perPage);
    }
}
