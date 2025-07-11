<?php

namespace App\Services\AI;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;
use Exception;

class OpenRouter extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $apiKey = $credentials['api_key'];

        try {
            $url = 'https://openrouter.ai/api/v1/credits';
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ];

            $response = $this->makeHttpRequest($url, $options);

            // If we get a successful response, the API key is valid
            if ($response['status_code'] === 200) {
                $creditsData = $response['data'];

                return ValidationResult::success(
                    provider: 'OpenRouter',
                    message: "OpenRouter API key is valid and working.",
                    code: '200',
                    metadata: [
                        'credits' => $creditsData,
                    ]
                );
            } else {
                // If we get here, we have an error response but not an exception
                $errorData = $response['data']['error'] ?? null;
                $statusCode = $response['status_code'];
                $errorMessage = $errorData['message'] ?? 'Unknown error';

                return $this->handleErrorResponse($statusCode, $errorMessage);
            }

        } catch (Exception $e) {
            // For network errors or other exceptions
            return ValidationResult::failure(
                provider: 'OpenRouter',
                message: 'Connection error: ' . $e->getMessage(),
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
     * Handle error responses from the OpenRouter API
     */
    private function handleErrorResponse(int $statusCode, string $errorMessage): ValidationResult
    {
        $userMessage = match ($statusCode) {
            400 => 'Bad Request - Invalid or missing parameters, or CORS issue',
            401 => 'Invalid credentials - OAuth session expired or disabled/invalid API key',
            402 => 'Insufficient credits - Your account or API key has insufficient credits',
            403 => 'Content flagged - Your chosen model requires moderation and your input was flagged',
            408 => 'Request timeout - Your request timed out',
            429 => 'Rate limited - You are being rate limited',
            502 => 'Bad Gateway - Your chosen model is down or we received an invalid response from it',
            503 => 'Service Unavailable - No available model provider meets your routing requirements',
            default => 'An unexpected error occurred: ' . $errorMessage,
        };

        $status = match ($statusCode) {
            400 => ValidationStatus::INVALID,
            401 => ValidationStatus::INVALID,
            402 => ValidationStatus::INVALID,
            403 => ValidationStatus::FORBIDDEN,
            408 => ValidationStatus::UNAVAILABLE,
            429 => ValidationStatus::RATE_LIMITED,
            502, 503 => ValidationStatus::UNAVAILABLE,
            default => ValidationStatus::FAILED,
        };

        return ValidationResult::failure(
            provider: 'OpenRouter',
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
