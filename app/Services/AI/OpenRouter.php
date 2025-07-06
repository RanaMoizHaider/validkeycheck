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

class OpenRouter extends AbstractServiceProvider
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
                ->using(Provider::OpenRouter, 'meta-llama/llama-3.2-3b-instruct:free')
                ->usingProviderConfig(['api_key' => $apiKey])
                ->withSchema($schema)
                ->withPrompt('Validate this API key by responding with: status="success", provider="OpenRouter", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asStructured();

            $validationData = $response->structured;

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'OpenRouter',
                    message: "OpenRouter API key is valid and working.",
                    code: '200',
                    metadata: [
                        'model' => 'meta-llama/llama-3.2-3b-instruct:free',
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'OpenRouter',
                    message: 'OpenRouter API key validation failed.',
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
                400 => 'Bad Request - Invalid or missing parameters, or CORS issue',
                401 => 'Invalid credentials - OAuth session expired or disabled/invalid API key',
                402 => 'Insufficient credits - Your account or API key has insufficient credits',
                403 => 'Content flagged - Your chosen model requires moderation and your input was flagged',
                408 => 'Request timeout - Your request timed out',
                429 => 'Rate limited - You are being rate limited',
                502 => 'Model unavailable - Your chosen model is down or returned an invalid response',
                503 => 'No available providers - No model provider meets your routing requirements',
                default => 'An unexpected error occurred while validating the API key',
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
    }

    public function getRequiredFields(): array
    {
        return [
            'api_key' => 'API Key',
        ];
    }
} 