<?php

namespace App\Services\AI;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;

class Perplexity extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $apiKey = $credentials['api_key'];

        try {
            $response = $this->makeHttpRequest(
                'https://api.perplexity.ai/chat/completions',
                [
                    'method' => 'POST',
                    'headers' => [
                        'Authorization' => 'Bearer '.$apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'data' => [
                        'model' => 'sonar',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => 'Respond with only the word "Working" if you can see this message.',
                            ],
                        ],
                        'max_tokens' => 10,
                        'temperature' => 0.1,
                    ],
                    'timeout' => 30,
                ]
            );

            $statusCode = $response['status_code'];
            $data = $response['data'];

            if ($statusCode === 200) {
                return ValidationResult::success(
                    provider: 'Perplexity',
                    message: 'Perplexity API key is valid and working.',
                    code: '200',
                    metadata: [
                        'model' => 'sonar',
                        'response_preview' => $data['choices'][0]['message']['content'] ?? null,
                        'usage' => $data['usage'] ?? null,
                    ]
                );
            } else {
                $userMessage = match ($statusCode) {
                    400 => 'Invalid request format or missing parameters',
                    401 => 'Invalid API key provided',
                    403 => 'Access forbidden - check your API key permissions',
                    404 => 'Requested resource not found',
                    429 => 'Rate limit exceeded - please try again later',
                    500 => 'Perplexity service temporarily unavailable',
                    503 => 'Perplexity service is currently overloaded - please try again later',
                    default => 'Perplexity API request failed',
                };

                $status = match ($statusCode) {
                    429 => ValidationStatus::RATE_LIMITED,
                    500, 503 => ValidationStatus::UNAVAILABLE,
                    default => ValidationStatus::INVALID,
                };

                return ValidationResult::failure(
                    provider: 'Perplexity',
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
                provider: 'Perplexity',
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
