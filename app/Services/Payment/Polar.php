<?php

namespace App\Services\Payment;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;
use Exception;

class Polar extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $apiKey = $credentials['api_key'];

        try {
            $url = 'https://api.polar.sh/v1/organizations/';
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ];

            $response = $this->makeHttpRequest($url, $options);

            // If we get a successful response, the API key is valid
            if ($response['status_code'] === 200) {
                $organizationsData = $response['data'];

                // Extract organization names for metadata
                $organizationNames = [];
                if (isset($organizationsData['items']) && is_array($organizationsData['items'])) {
                    foreach ($organizationsData['items'] as $organization) {
                        if (isset($organization['name'])) {
                            $organizationNames[] = $organization['name'];
                        }
                    }
                }

                return ValidationResult::success(
                    provider: 'Polar',
                    message: 'Polar API key is valid and working.',
                    code: '200',
                    metadata: [
                        'organizations' => $organizationsData,
                        'organization_names' => $organizationNames,
                    ]
                );
            } else {
                // If we get here, we have an error response but not an exception
                $errorData = $response['data']['detail'] ?? null;
                $statusCode = $response['status_code'];
                $errorMessage = is_array($errorData) && ! empty($errorData) ? $errorData[0]['msg'] ?? 'Unknown error' : 'Unknown error';

                return $this->handleErrorResponse($statusCode, $errorMessage);
            }

        } catch (Exception $e) {
            // For network errors or other exceptions
            return ValidationResult::failure(
                provider: 'Polar',
                message: 'Connection error: '.$e->getMessage(),
                status: ValidationStatus::FAILED,
                code: null,
                metadata: [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Handle error responses from the Polar API
     */
    private function handleErrorResponse(int $statusCode, string $errorMessage): ValidationResult
    {
        $userMessage = match ($statusCode) {
            400 => 'Bad Request - Invalid or missing parameters',
            401 => 'Invalid credentials - Invalid API key',
            403 => 'Forbidden - You do not have permission to access this resource',
            404 => 'Not Found - The requested resource was not found',
            422 => 'Validation Error - '.$errorMessage,
            429 => 'Rate limited - You are being rate limited',
            500 => 'Server Error - An error occurred on the server',
            default => 'An unexpected error occurred: '.$errorMessage,
        };

        $status = match ($statusCode) {
            400, 401, 404, 422 => ValidationStatus::INVALID,
            403 => ValidationStatus::FORBIDDEN,
            429 => ValidationStatus::RATE_LIMITED,
            500 => ValidationStatus::UNAVAILABLE,
            default => ValidationStatus::FAILED,
        };

        return ValidationResult::failure(
            provider: 'Polar',
            message: $userMessage,
            status: $status,
            code: $statusCode
        );
    }

    public function getRequiredFields(): array
    {
        return [
            'api_key' => 'API Key',
        ];
    }
}
