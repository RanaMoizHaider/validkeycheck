<?php

namespace App\Services\AI;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;

class Exa extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $apiKey = $credentials['api_key'];

        try {
            $response = $this->makeHttpRequest(
                'https://api.exa.ai/answer',
                [
                    'method' => 'POST',
                    'headers' => [
                        'x-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'data' => [
                        'query' => 'What is Exa? Answer in 5 words starting with \'Exa is\'.',
                        'stream' => false,
                        'text' => false,
                    ],
                    'timeout' => 30,
                ]
            );

            $statusCode = $response['status_code'];
            $data = $response['data'];

            if ($statusCode === 200) {
                $costDollars = $data['costDollars'] ?? null;
                $totalCost = $costDollars['total'] ?? 0;

                return ValidationResult::success(
                    provider: 'Exa',
                    message: 'Exa API key is valid and working.',
                    code: '200',
                    metadata: [
                        'answer' => $data['answer'] ?? null,
                        'total_cost' => $totalCost,
                    ]
                );
            } else {
                $userMessage = match ($statusCode) {
                    400 => 'Invalid request format or missing parameters',
                    401 => 'Invalid API key provided',
                    403 => 'Access forbidden - check your API key permissions',
                    404 => 'Requested resource not found',
                    429 => 'Rate limit exceeded - please try again later',
                    500 => 'Exa service temporarily unavailable',
                    503 => 'Exa service is currently overloaded - please try again later',
                    default => 'Exa API request failed',
                };

                $status = match ($statusCode) {
                    429 => ValidationStatus::RATE_LIMITED,
                    500, 503 => ValidationStatus::UNAVAILABLE,
                    default => ValidationStatus::INVALID,
                };

                return ValidationResult::failure(
                    provider: 'Exa',
                    message: $userMessage,
                    status: $status,
                    code: $statusCode,
                    metadata: [
                        'response_body' => $response['body'] ?? null,
                    ]
                );
            }

        } catch (\Exception $e) {
            return ValidationResult::failure(
                provider: 'Exa',
                message: 'Request timeout or connection error',
                status: ValidationStatus::UNAVAILABLE,
                code: null,
                metadata: [
                    'error_details' => $e->getMessage(),
                ]
            );
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'api_key' => 'API Key',
        ];
    }
}
