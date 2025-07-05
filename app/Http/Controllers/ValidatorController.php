<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateProviderRequest;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ValidatorController extends Controller
{
    public function index()
    {
        $categories = Category::with('services')
            ->get()
            ->filter(fn($category) => $category->services->isNotEmpty())
            ->map(fn($category) => [
                ...$category->only(['slug', 'name', 'description']),
                'providers' => $category->services
            ])
            ->values();

        return Inertia::render('validator', [
            'categories' => $categories
        ]);
    }

    public function validate(ValidateProviderRequest $request)
    {
        $service = Service::where('slug', $request->validated('provider'))->first();
        
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
                'metadata' => null,
            ], 404);
        }

        try {
            $providerInstance = $service->createInstance();
            $result = $providerInstance->validate($request->validated('credentials'));

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'provider' => $service->name,
                'metadata' => $result['metadata'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'provider' => $service->name,
                'metadata' => null,
            ], 500);
        }
    }
} 