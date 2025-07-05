<?php

namespace App\Services;

use App\Contracts\ServiceProviderInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    protected ?object $serviceData = null;

    public function __construct(?object $serviceData = null)
    {
        $this->serviceData = $serviceData;
    }

    public function getName(): string
    {
        return $this->serviceData?->name ?? 'Unknown Service';
    }

    public function getSlug(): string
    {
        return $this->serviceData?->slug ?? 'unknown';
    }

    public function getCategory(): string
    {
        return $this->serviceData?->category ?? 'unknown';
    }

    public function getDescription(): string
    {
        return $this->serviceData?->description ?? 'No description available';
    }

    public function getRequiredFields(): array
    {
        return $this->serviceData?->required_fields ?? [];
    }
    abstract protected function performValidation(array $credentials): array;

    public function validate(array $credentials): array
    {
        try {
            // Validate required fields
            $missingFields = $this->validateRequiredFields($credentials);
            if (!empty($missingFields)) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                    'metadata' => null,
                ];
            }
            
            // Perform the actual validation
            return $this->performValidation($credentials);
            
        } catch (Exception $e) {
            Log::error('Service provider validation error', [
                'provider' => $this->getSlug(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred during validation: ' . $e->getMessage(),
                'metadata' => null,
            ];
        }
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->serviceData?->website_url;
    }

    public function getApiKeysUrl(): ?string
    {
        return $this->serviceData?->api_keys_url;
    }

    public function getBaseUrl(): ?string
    {
        return $this->serviceData?->base_url;
    }

    public function getDocumentationUrl(): ?string
    {
        return $this->serviceData?->documentation_url;
    }

    protected function validateRequiredFields(array $credentials): array
    {
        $missing = [];
        foreach ($this->getRequiredFields() as $field) {
            if (empty($credentials[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    protected function makeHttpRequest(string $url, array $options = []): array
    {
        $response = Http::timeout(30)->retry(2, 1000);
        
        if (isset($options['headers'])) {
            $response = $response->withHeaders($options['headers']);
        }
        
        if (isset($options['method']) && strtoupper($options['method']) === 'POST') {
            $response = $response->post($url, $options['data'] ?? []);
        } else {
            $response = $response->get($url, $options['query'] ?? []);
        }

        return [
            'status_code' => $response->status(),
            'data' => $response->json(),
            'response' => $response
        ];
    }
} 