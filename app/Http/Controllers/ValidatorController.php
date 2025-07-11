<?php

namespace App\Http\Controllers;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Http\Requests\ValidateProviderRequest;
use App\Models\Category;
use App\Models\Service;
use Inertia\Inertia;

class ValidatorController extends Controller
{
    public function index()
    {
        $categories = Category::with('services')
            ->get()
            ->filter(fn ($category) => $category->services->isNotEmpty())
            ->map(fn ($category) => [
                ...$category->only(['slug', 'name', 'description']),
                'providers' => $category->services,
            ])
            ->values();

        return Inertia::render('validator', [
            'categories' => $categories,
            'currentYear' => date('Y'),
        ]);
    }

    public function validate(ValidateProviderRequest $request)
    {
        $service = Service::where('slug', $request->validated('provider'))->first();

        if (! $service) {
            return response()->json(
                ValidationResult::failure(
                    'Service not found',
                    'not_found',
                    ValidationStatus::UNAVAILABLE
                )->toArray(),
                404
            );
        }

        try {
            $providerInstance = $service->createInstance();
            $result = $providerInstance->validate($request->validated('credentials'));

            // Laravel Data automatically handles JSON serialization
            return response()->json($result->toArray());
        } catch (\Exception $e) {
            return response()->json(
                ValidationResult::failure(
                    $e->getMessage(),
                    $service->name,
                    ValidationStatus::UNAVAILABLE,
                    $e->getCode() ? (string) $e->getCode() : null
                )->toArray(),
                500
            );
        }
    }
}
