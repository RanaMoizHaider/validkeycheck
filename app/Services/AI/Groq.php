<?php

namespace App\Services\AI;

use App\Services\AbstractServiceProvider;
use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Exceptions\PrismException;

class Groq extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $apiKey = $credentials['api_key'];

        try {
            // Define a schema for structured validation response
            $schema = new ObjectSchema(
                name: 'validation_response',
                description: 'API key validation response',
                properties: [
                    new StringSchema('status', 'Validation status'),
                    new StringSchema('provider', 'Provider name'),
                    new BooleanSchema('is_valid', 'Whether the API key is valid'),
                    new StringSchema('message', 'Validation message'),
                ],
                requiredFields: ['status', 'provider', 'is_valid', 'message']
            );

            // Use Prism to validate the API key with structured output
            $response = Prism::structured()
                ->using(Provider::Groq, 'llama-3.3-70b-versatile')
                ->usingProviderConfig([
                    'api_key' => $apiKey,
                ])
                ->withSchema($schema)
                ->withPrompt('Validate this API key by responding with: status="success", provider="Groq", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asStructured();

            $validationData = $response->structured;

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'Groq',
                    message: "Groq API key is valid and working.",
                    code: '200',
                    metadata: [
                        'model' => 'llama-3.3-70b-versatile',
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'Groq',
                    message: 'Groq API key validation failed.',
                    status: ValidationStatus::INVALID,
                    code: null,
                    metadata: [
                        'validation_data' => $validationData,
                        'response_text' => $response->text ?? null,
                    ]
                );
            }

        } catch (PrismException $e) {
            $statusCode = $e->getPrevious()->getCode();

            $userMessage = match ($statusCode) {
                400 => 'Bad request - Invalid or missing parameters in the request',
                401 => 'Unauthorized - Invalid or missing API key',
                403 => 'Forbidden - Access denied or insufficient permissions',
                404 => 'Not found - The requested resource or model does not exist',
                422 => 'Unprocessable entity - Invalid input data or parameters',
                429 => 'Rate limit exceeded - Too many requests, please try again later',
                500 => 'Internal server error - Unexpected error on the server',
                502 => 'Bad gateway - Invalid response from upstream server',
                503 => 'Service unavailable - The service is temporarily unavailable',
                504 => 'Gateway timeout - Request timeout from upstream server',
                default => 'An unexpected error occurred while validating the API key',
            };

            $status = match ($statusCode) {
                400, 422 => ValidationStatus::INVALID,
                401, 403 => ValidationStatus::INVALID,
                404 => ValidationStatus::INVALID,
                429 => ValidationStatus::RATE_LIMITED,
                500, 502, 503, 504 => ValidationStatus::UNAVAILABLE,
                default => ValidationStatus::FAILED,
            };

            return ValidationResult::failure(
                provider: 'Groq',
                message: $userMessage,
                status: $status,
                code: $statusCode
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