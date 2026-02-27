<?php

namespace Tests\Feature;

use App\Models\Disease;
use App\Models\DigitalTwin;
use App\Models\HealthProfile;
use App\Models\Simulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Black-Box Test Suite — Simulation Endpoints
 *
 * Routes under test:
 *   POST   /api/simulations/run         (SimulationController@run)
 *   GET    /api/simulations             (SimulationController@index)
 *   GET    /api/simulations/{id}        (SimulationController@show)
 *
 * Testing strategy: external (black-box) — inputs & HTTP responses only.
 * No internal implementation details are asserted.
 */
class SimulationBlackBoxTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────────────

    /** Authenticated user with an active DigitalTwin (minimum viable setup). */
    private function userWithTwin(): User
    {
        $user = User::factory()->create();

        HealthProfile::factory()->create(['user_id' => $user->id]);

        DigitalTwin::factory()->create([
            'user_id'            => $user->id,
            'is_active'          => true,
            'overall_risk_score' => 6.5,
            'risk_category'      => 'high',
            'snapshot_data'      => [
                'health_profile' => [
                    'disease_type'     => 'diabetes',
                    'avg_sleep_hours'  => 7,
                    'stress_level'     => 'medium',
                    'eating_habits'    => 'moderate',
                ],
                'diabetes' => [
                    'avg_blood_sugar' => 160,
                    'sugar_cravings'  => 'frequent',
                ],
            ],
        ]);

        return $user;
    }

    /** Authenticated user with NO active DigitalTwin. */
    private function userWithoutTwin(): User
    {
        return User::factory()->create();
    }

    /** Baseline valid payload for POST /api/simulations/run. */
    private function validRunPayload(array $overrides = []): array
    {
        return array_merge([
            'type'        => 'meal',
            'description' => 'Replacing white rice with brown rice for lunch.',
        ], $overrides);
    }

    private function runHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }

    // ════════════════════════════════════════════════════════════════════════
    // TC-SIM-01 … TC-SIM-20  │  POST /api/simulations/run
    // ════════════════════════════════════════════════════════════════════════

    /** TC-SIM-01: Valid meal simulation returns 201 with success payload. */
    public function test_run_meal_simulation_returns_201(): void
    {
        $user = $this->userWithTwin();

        $response = $this->actingAs($user)
            ->postJson('/api/simulations/run', $this->validRunPayload(), $this->runHeaders());

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'type',
                         'input_data',
                         'original_risk_score',
                         'simulated_risk_score',
                         'risk_change',
                         'risk_category_before',
                         'risk_category_after',
                         'rag_explanation',
                         'rag_confidence',
                         'results',
                         'alerts',
                         'created_at',
                     ],
                 ])
                 ->assertJson(['success' => true]);
    }

    /** TC-SIM-02: Valid sleep simulation (with sleep_hours parameter). */
    public function test_run_sleep_simulation_with_sleep_hours(): void
    {
        $user = $this->userWithTwin();

        $response = $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type'        => 'sleep',
            'description' => 'Getting 8 hours of quality sleep.',
            'parameters'  => ['sleep_hours' => 8],
        ]));

        $response->assertStatus(201)->assertJson(['success' => true]);
        $response->assertJsonPath('data.type', 'sleep');
    }

    /** TC-SIM-03: Valid stress simulation (with stress_level parameter). */
    public function test_run_stress_simulation_with_stress_level(): void
    {
        $user = $this->userWithTwin();

        $response = $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type'        => 'stress',
            'description' => 'Practicing daily meditation to reduce stress.',
            'parameters'  => ['stress_level' => 'low'],
        ]));

        $response->assertStatus(201)->assertJson(['success' => true]);
        $response->assertJsonPath('data.type', 'stress');
    }

    /** TC-SIM-04: Unauthenticated request is rejected with 401. */
    public function test_run_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/simulations/run', $this->validRunPayload())
             ->assertStatus(401);
    }

    /** TC-SIM-05: Missing required field `type` returns 422. */
    public function test_run_missing_type_returns_422(): void
    {
        $user = $this->userWithTwin();
        $payload = $this->validRunPayload();
        unset($payload['type']);

        $this->actingAs($user)->postJson('/api/simulations/run', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['type']);
    }

    /** TC-SIM-06: Invalid `type` value returns 422. */
    public function test_run_invalid_type_returns_422(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type' => 'exercise', // not in: meal,sleep,stress
        ]))->assertStatus(422)->assertJsonValidationErrors(['type']);
    }

    /** TC-SIM-07: Missing required field `description` returns 422. */
    public function test_run_missing_description_returns_422(): void
    {
        $user = $this->userWithTwin();
        $payload = $this->validRunPayload();
        unset($payload['description']);

        $this->actingAs($user)->postJson('/api/simulations/run', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['description']);
    }

    /** TC-SIM-08: `description` exceeding 500 characters returns 422. */
    public function test_run_description_too_long_returns_422(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'description' => str_repeat('x', 501),
        ]))->assertStatus(422)->assertJsonValidationErrors(['description']);
    }

    /** TC-SIM-09: `description` at exactly 500 characters is valid. */
    public function test_run_description_at_max_length_is_accepted(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'description' => str_repeat('a', 500),
        ]))->assertStatus(201);
    }

    /** TC-SIM-10: `parameters.sleep_hours` above max (>24) returns 422. */
    public function test_run_sleep_hours_above_max_returns_422(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type'       => 'sleep',
            'parameters' => ['sleep_hours' => 25],
        ]))->assertStatus(422)->assertJsonValidationErrors(['parameters.sleep_hours']);
    }

    /** TC-SIM-11: `parameters.sleep_hours` below min (<0) returns 422. */
    public function test_run_sleep_hours_below_min_returns_422(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type'       => 'sleep',
            'parameters' => ['sleep_hours' => -1],
        ]))->assertStatus(422)->assertJsonValidationErrors(['parameters.sleep_hours']);
    }

    /** TC-SIM-12: Boundary — `parameters.sleep_hours` = 0 is valid. */
    public function test_run_sleep_hours_at_zero_is_valid(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type'       => 'sleep',
            'parameters' => ['sleep_hours' => 0],
        ]))->assertStatus(201);
    }

    /** TC-SIM-13: Boundary — `parameters.sleep_hours` = 24 is valid. */
    public function test_run_sleep_hours_at_max_boundary_is_valid(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type'       => 'sleep',
            'parameters' => ['sleep_hours' => 24],
        ]))->assertStatus(201);
    }

    /** TC-SIM-14: Invalid `parameters.stress_level` returns 422. */
    public function test_run_invalid_stress_level_returns_422(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'type'       => 'stress',
            'parameters' => ['stress_level' => 'extreme'], // not in: low,medium,high
        ]))->assertStatus(422)->assertJsonValidationErrors(['parameters.stress_level']);
    }

    /** TC-SIM-15: All valid stress levels are accepted. */
    public function test_run_all_valid_stress_levels_are_accepted(): void
    {
        $user = $this->userWithTwin();

        foreach (['low', 'medium', 'high'] as $level) {
            $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
                'type'       => 'stress',
                'parameters' => ['stress_level' => $level],
            ]))->assertStatus(201, "Expected 201 for stress_level=$level");
        }
    }

    /** TC-SIM-16: User without an active Digital Twin receives a 422/500 error. */
    public function test_run_without_digital_twin_returns_error(): void
    {
        $user = $this->userWithoutTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload())
             ->assertStatus(422); // service throws RuntimeException → mapped to 422
    }

    /** TC-SIM-17: `parameters` is optional — omitting it succeeds. */
    public function test_run_without_parameters_is_valid(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', [
            'type'        => 'meal',
            'description' => 'Eating more salad.',
        ])->assertStatus(201);
    }

    /** TC-SIM-18: `parameters` must be an array, not a scalar. */
    public function test_run_parameters_as_string_returns_422(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'parameters' => 'sleep_hours=8',
        ]))->assertStatus(422)->assertJsonValidationErrors(['parameters']);
    }

    /** TC-SIM-19: Keyword-matched meal description produces a valid simulation result. */
    public function test_run_reduce_sugar_meal_produces_valid_result(): void
    {
        $user = $this->userWithTwin();

        $resp = $this->actingAs($user)->postJson('/api/simulations/run', $this->validRunPayload([
            'description' => 'reduce sugar intake completely.',
        ]))->assertStatus(201);

        $data = $resp->json('data');
        // Risk change must be present and numeric
        $this->assertNotNull($data['risk_change']);
        $this->assertIsNumeric($data['risk_change']);
    }

    /** TC-SIM-20: Response contains numeric risk scores. */
    public function test_run_response_contains_numeric_risk_scores(): void
    {
        $user = $this->userWithTwin();

        $data = $this->actingAs($user)
            ->postJson('/api/simulations/run', $this->validRunPayload())
            ->assertStatus(201)
            ->json('data');

        $this->assertIsNumeric($data['original_risk_score']);
        $this->assertIsNumeric($data['simulated_risk_score']);
        $this->assertIsNumeric($data['risk_change']);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TC-SIM-21 … TC-SIM-30  │  GET /api/simulations
    // ════════════════════════════════════════════════════════════════════════

    /** TC-SIM-21: Authenticated user can list their simulations. */
    public function test_index_returns_paginated_simulations(): void
    {
        $user = $this->userWithTwin();
        Simulation::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)->getJson('/api/simulations')
             ->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data',
                 'meta' => ['current_page', 'last_page', 'per_page', 'total'],
             ])
             ->assertJson(['success' => true]);
    }

    /** TC-SIM-22: Unauthenticated GET /api/simulations returns 401. */
    public function test_index_unauthenticated_returns_401(): void
    {
        $this->getJson('/api/simulations')->assertStatus(401);
    }

    /** TC-SIM-23: User with no simulations receives empty data array. */
    public function test_index_returns_empty_for_user_with_no_simulations(): void
    {
        $user = $this->userWithTwin();

        $resp = $this->actingAs($user)->getJson('/api/simulations')->assertStatus(200);
        $this->assertEmpty($resp->json('data'));
        $this->assertEquals(0, $resp->json('meta.total'));
    }

    /** TC-SIM-24: `per_page` query parameter is respected. */
    public function test_index_per_page_parameter_is_respected(): void
    {
        $user = $this->userWithTwin();
        Simulation::factory()->count(10)->create(['user_id' => $user->id]);

        $resp = $this->actingAs($user)->getJson('/api/simulations?per_page=3')->assertStatus(200);
        $this->assertEquals(3, $resp->json('meta.per_page'));
        $this->assertCount(3, $resp->json('data'));
    }

    /** TC-SIM-25: User sees only their own simulations, not other users'. */
    public function test_index_only_returns_own_simulations(): void
    {
        $user1 = $this->userWithTwin();
        $user2 = User::factory()->create();
        Simulation::factory()->count(5)->create(['user_id' => $user1->id]);
        Simulation::factory()->count(3)->create(['user_id' => $user2->id]);

        $resp = $this->actingAs($user1)->getJson('/api/simulations')->assertStatus(200);
        $this->assertEquals(5, $resp->json('meta.total'));
    }

    /** TC-SIM-26: `meta.current_page` is 1 by default. */
    public function test_index_meta_current_page_defaults_to_1(): void
    {
        $user = $this->userWithTwin();

        $resp = $this->actingAs($user)->getJson('/api/simulations')->assertStatus(200);
        $this->assertEquals(1, $resp->json('meta.current_page'));
    }

    /** TC-SIM-27: Default `per_page` is 15. */
    public function test_index_default_per_page_is_15(): void
    {
        $user = $this->userWithTwin();
        Simulation::factory()->count(20)->create(['user_id' => $user->id]);

        $resp = $this->actingAs($user)->getJson('/api/simulations')->assertStatus(200);
        $this->assertEquals(15, $resp->json('meta.per_page'));
        $this->assertCount(15, $resp->json('data'));
    }

    /** TC-SIM-28: `meta.last_page` reflects total pages correctly. */
    public function test_index_meta_last_page_is_correct(): void
    {
        $user = $this->userWithTwin();
        Simulation::factory()->count(30)->create(['user_id' => $user->id]);

        $resp = $this->actingAs($user)->getJson('/api/simulations?per_page=10')->assertStatus(200);
        $this->assertEquals(3, $resp->json('meta.last_page'));
    }

    /** TC-SIM-29: Each simulation item contains standard resource fields. */
    public function test_index_each_item_has_required_fields(): void
    {
        $user = $this->userWithTwin();
        Simulation::factory()->create(['user_id' => $user->id]);

        $resp = $this->actingAs($user)->getJson('/api/simulations')->assertStatus(200);
        $item = $resp->json('data.0');

        foreach (['id', 'type', 'original_risk_score', 'simulated_risk_score', 'risk_change', 'created_at'] as $field) {
            $this->assertArrayHasKey($field, $item, "Field '$field' missing from simulation item.");
        }
    }

    /** TC-SIM-30: Page 2 returns next set of records. */
    public function test_index_pagination_page_2_returns_next_records(): void
    {
        $user = $this->userWithTwin();
        Simulation::factory()->count(20)->create(['user_id' => $user->id]);

        $page1Ids = $this->actingAs($user)->getJson('/api/simulations?per_page=10')->json('data.*.id');
        $page2Ids = $this->actingAs($user)->getJson('/api/simulations?per_page=10&page=2')->json('data.*.id');

        $this->assertEmpty(array_intersect($page1Ids, $page2Ids), 'Pages must not overlap.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // TC-SIM-31 … TC-SIM-40  │  GET /api/simulations/{id}
    // ════════════════════════════════════════════════════════════════════════

    /** TC-SIM-31: Owner can fetch their simulation by ID. */
    public function test_show_owner_can_fetch_simulation(): void
    {
        $user = $this->userWithTwin();
        $sim  = Simulation::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->getJson("/api/simulations/{$sim->id}")
             ->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => [
                     'id', 'type', 'input_data',
                     'original_risk_score', 'simulated_risk_score',
                     'risk_change', 'rag_explanation', 'results', 'alerts',
                 ],
             ])
             ->assertJsonPath('data.id', $sim->id)
             ->assertJson(['success' => true]);
    }

    /** TC-SIM-32: Non-existent simulation ID returns 404. */
    public function test_show_nonexistent_id_returns_404(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->getJson('/api/simulations/99999')
             ->assertStatus(404)
             ->assertJson(['success' => false]);
    }

    /** TC-SIM-33: Another user's simulation ID returns 404 (ownership check). */
    public function test_show_another_users_simulation_returns_404(): void
    {
        $owner = $this->userWithTwin();
        $other = User::factory()->create();
        $sim   = Simulation::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->getJson("/api/simulations/{$sim->id}")
             ->assertStatus(404)
             ->assertJson(['success' => false]);
    }

    /** TC-SIM-34: Unauthenticated GET /api/simulations/{id} returns 401. */
    public function test_show_unauthenticated_returns_401(): void
    {
        $user = $this->userWithTwin();
        $sim  = Simulation::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/simulations/{$sim->id}")->assertStatus(401);
    }

    /** TC-SIM-35: ID = 0 (invalid) returns 404. */
    public function test_show_id_zero_returns_404(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->getJson('/api/simulations/0')->assertStatus(404);
    }

    /** TC-SIM-36: Non-numeric ID returns 404. */
    public function test_show_non_numeric_id_returns_404(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->getJson('/api/simulations/abc')->assertStatus(404);
    }

    /** TC-SIM-37: Response includes `alerts` array (eager-loaded). */
    public function test_show_response_includes_alerts_array(): void
    {
        $user = $this->userWithTwin();
        $sim  = Simulation::factory()->create(['user_id' => $user->id]);

        $data = $this->actingAs($user)->getJson("/api/simulations/{$sim->id}")
                     ->assertStatus(200)
                     ->json('data');

        $this->assertArrayHasKey('alerts', $data);
        $this->assertIsArray($data['alerts']);
    }

    /** TC-SIM-38: `risk_change` = simulated_risk_score - original_risk_score. */
    public function test_show_risk_change_is_mathematically_consistent(): void
    {
        $user = $this->userWithTwin();
        $sim  = Simulation::factory()->create([
            'user_id'              => $user->id,
            'original_risk_score'  => 6.00,
            'simulated_risk_score' => 7.50,
            'risk_change'          => 1.50,
        ]);

        $data = $this->actingAs($user)->getJson("/api/simulations/{$sim->id}")
                     ->assertStatus(200)->json('data');

        $computed = round($data['simulated_risk_score'] - $data['original_risk_score'], 2);
        $this->assertEqualsWithDelta($data['risk_change'], $computed, 0.01);
    }

    /** TC-SIM-39: `created_at` is returned in ISO 8601 format. */
    public function test_show_created_at_is_iso8601(): void
    {
        $user = $this->userWithTwin();
        $sim  = Simulation::factory()->create(['user_id' => $user->id]);

        $createdAt = $this->actingAs($user)->getJson("/api/simulations/{$sim->id}")
                          ->assertStatus(200)->json('data.created_at');

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $createdAt
        );
    }

    /** TC-SIM-40: Very large `id` (INT overflow candidate) returns 404 gracefully. */
    public function test_show_very_large_id_returns_404(): void
    {
        $user = $this->userWithTwin();

        $this->actingAs($user)->getJson('/api/simulations/9999999999')->assertStatus(404);
    }
}
