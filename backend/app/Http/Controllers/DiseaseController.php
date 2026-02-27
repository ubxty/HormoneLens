<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Repositories\DiseaseRepository;
use Illuminate\Http\Request;

class DiseaseController extends Controller
{
    public function __construct(
        private readonly DiseaseRepository $diseaseRepo,
    ) {}

    /**
     * List all active diseases (with field definitions).
     * GET /api/diseases
     */
    public function index()
    {
        $diseases = $this->diseaseRepo->allActive();

        return response()->json([
            'success' => true,
            'data' => $diseases->map(fn (Disease $d) => [
                'id' => $d->id,
                'slug' => $d->slug,
                'name' => $d->name,
                'icon' => $d->icon,
                'description' => $d->description,
                'fields' => $d->fields->map(fn ($f) => [
                    'slug' => $f->slug,
                    'label' => $f->label,
                    'field_type' => $f->field_type,
                    'category' => $f->category,
                    'options' => $f->options,
                    'is_required' => $f->is_required,
                    'sort_order' => $f->sort_order,
                ]),
            ]),
        ]);
    }

    /**
     * Show a disease definition + the authenticated user's data.
     * GET /api/diseases/{slug}
     */
    public function show(Request $request, string $slug)
    {
        $disease = $this->diseaseRepo->findBySlug($slug);

        if (!$disease || !$disease->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Disease type not found.',
            ], 404);
        }

        $userData = $this->diseaseRepo->findUserData($request->user(), $disease);

        return response()->json([
            'success' => true,
            'data' => [
                'disease' => [
                    'id' => $disease->id,
                    'slug' => $disease->slug,
                    'name' => $disease->name,
                    'icon' => $disease->icon,
                    'description' => $disease->description,
                    'fields' => $disease->fields->map(fn ($f) => [
                        'slug' => $f->slug,
                        'label' => $f->label,
                        'field_type' => $f->field_type,
                        'category' => $f->category,
                        'options' => $f->options,
                        'is_required' => $f->is_required,
                        'sort_order' => $f->sort_order,
                    ]),
                ],
                'field_values' => $userData?->field_values ?? (object) [],
            ],
        ]);
    }

    /**
     * Store/update user's data for a disease.
     * POST /api/diseases/{slug}
     */
    public function store(Request $request, string $slug)
    {
        $disease = $this->diseaseRepo->findBySlug($slug);

        if (!$disease || !$disease->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Disease type not found.',
            ], 404);
        }

        // Build & validate rules dynamically from field definitions
        $rules = $disease->buildValidationRules();
        $validated = $request->validate($rules);

        $fieldValues = $validated['field_values'] ?? [];

        // Ensure only defined field slugs are stored
        $allowedSlugs = $disease->fields->pluck('slug')->toArray();
        $fieldValues = array_intersect_key($fieldValues, array_flip($allowedSlugs));

        // Cast types based on field definitions
        foreach ($disease->fields as $field) {
            if (!array_key_exists($field->slug, $fieldValues)) {
                continue;
            }
            $fieldValues[$field->slug] = match ($field->field_type) {
                'number' => is_numeric($fieldValues[$field->slug]) ? (float) $fieldValues[$field->slug] : $fieldValues[$field->slug],
                'boolean' => filter_var($fieldValues[$field->slug], FILTER_VALIDATE_BOOLEAN),
                default => $fieldValues[$field->slug],
            };
        }

        $data = $this->diseaseRepo->createOrUpdate(
            $request->user(),
            $disease,
            $fieldValues,
        );

        return response()->json([
            'success' => true,
            'message' => "{$disease->name} data saved successfully.",
            'data' => [
                'disease_slug' => $disease->slug,
                'field_values' => $data->field_values,
            ],
        ], 201);
    }

    /**
     * Get all disease data for the authenticated user.
     * GET /api/diseases/my-data
     */
    public function myData(Request $request)
    {
        $allData = $this->diseaseRepo->allUserData($request->user());

        return response()->json([
            'success' => true,
            'data' => $allData->map(fn ($d) => [
                'disease_slug' => $d->disease->slug,
                'disease_name' => $d->disease->name,
                'disease_icon' => $d->disease->icon,
                'field_values' => $d->field_values,
                'updated_at' => $d->updated_at?->toIso8601String(),
            ]),
        ]);
    }
}
