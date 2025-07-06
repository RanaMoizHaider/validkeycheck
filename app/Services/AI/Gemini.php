<?php

namespace App\Services\AI;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class Gemini extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $apiKey = $credentials['api_key'];

        try {
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

            $response = Prism::structured()
                ->using(Provider::Gemini, 'gemini-1.5-flash')
                ->usingProviderConfig(['api_key' => $apiKey])
                ->withSchema($schema)
                ->withPrompt('Validate this API key by responding with: status="success", provider="Gemini", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asStructured();

            $validationData = $response->structured;

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'Gemini',
                    message: "Gemini API key is valid and working.",
                    code: '200',
                    metadata: [
                        'model' => 'gemini-1.5-flash',
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'Gemini',
                    message: 'Gemini API key validation failed.',
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
                400 => 'Invalid request - The request was malformed or missing required parameters',
                401 => 'Authentication failed - Invalid API key provided',
                403 => 'Permission denied - The API key does not have permission to access this resource',
                404 => 'Model not found - The specified model does not exist',
                429 => 'Rate limit exceeded - Too many requests, please try again later',
                500 => 'Internal server error - An unexpected error occurred on the server',
                502 => 'Bad gateway - The server received an invalid response',
                503 => 'Service unavailable - The service is temporarily unavailable',
                default => 'An unexpected error occurred while validating the API key',
            };

            $status = match ($statusCode) {
                400, 404 => ValidationStatus::INVALID,
                401, 403 => ValidationStatus::INVALID,
                429 => ValidationStatus::RATE_LIMITED,
                500, 502, 503 => ValidationStatus::UNAVAILABLE,
                default => ValidationStatus::FAILED,
            };

            return ValidationResult::failure(
                provider: 'Gemini',
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