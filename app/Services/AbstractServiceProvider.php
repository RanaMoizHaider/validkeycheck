<?php

namespace App\Services;

use App\Contracts\ServiceProviderInterface;
use App\Data\ServiceData;
use App\Data\ValidationResult;
use App\Models\Service;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    protected ?object $serviceData = null;

    public function __construct(ServiceData|Service|null $serviceData = null)
    {
        $this->serviceData = $serviceData;
    }

    public function getName(): string
    {
        return $this->serviceData?->name ?? 'Unknown Service';
    }

    public function getSlug(): string
    {
        return $this->serviceData?->slug ?? 'unknown-service';
    }

    public function getCategory(): string
    {
        return $this->serviceData?->category ?? 'other';
    }

    public function getDescription(): string
    {
        return $this->serviceData?->description ?? 'No description available.';
    }

    public function validate(array $credentials): ValidationResult
    {
        try {
            $missingFields = $this->validateRequiredFields($credentials);
            if (!empty($missingFields)) {
                return ValidationResult::failure(
                    'Missing required fields: ' . implode(', ', $missingFields),
                    'validation_error'
                );
            }
            
            return $this->performValidation($credentials);
            
        } catch (Exception $e) {
            Log::error('Service provider validation error', [
                'provider' => $this->getSlug(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ValidationResult::fromException($e, $this->getName());
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
        $requiredFields = $this->getRequiredFieldsArray();

        foreach ($requiredFields as $field) {
            if (!isset($credentials[$field]) || $credentials[$field] === '') {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    /**
     * Get required fields as an array of field names
     */
    protected function getRequiredFieldsArray(): array
    {
        $requiredFields = $this->getRequiredFields();
        
        if (is_array($requiredFields)) {
            if (array_keys($requiredFields) !== range(0, count($requiredFields) - 1)) {
                return array_keys($requiredFields);
            } else {
                return $requiredFields;
            }
        }
        
        if ($this->serviceData && isset($this->serviceData->required_fields)) {
            $fields = is_string($this->serviceData->required_fields) 
                ? json_decode($this->serviceData->required_fields, true) 
                : $this->serviceData->required_fields;
            
            if (is_array($fields)) {
                return is_array($fields) && array_keys($fields) !== range(0, count($fields) - 1) 
                    ? array_keys($fields) 
                    : $fields;
            }
        }
        
        return [];
    }

    protected function makeHttpRequest(string $url, array $options = []): array
    {
        $method = $options['method'] ?? 'GET';
        $headers = $options['headers'] ?? [];
        $data = $options['data'] ?? [];
        $timeout = $options['timeout'] ?? 30;

        $httpClient = Http::timeout($timeout)->withHeaders($headers);

        if ($method === 'POST') {
            $response = $httpClient->post($url, $data);
        } elseif ($method === 'PUT') {
            $response = $httpClient->put($url, $data);
        } elseif ($method === 'DELETE') {
            $response = $httpClient->delete($url);
        } else {
            $response = $httpClient->get($url);
        }

        return [
            'status_code' => $response->status(),
            'body' => $response->body(),
            'data' => $response->json(),
        ];
    }

    /**
     * Perform the actual validation logic
     * This method should be implemented by each service provider
     */
    abstract protected function performValidation(array $credentials): ValidationResult;

    /**
     * Get the required fields for this service provider
     * This method should be implemented by each service provider
     */
    abstract public function getRequiredFields(): array;
} 