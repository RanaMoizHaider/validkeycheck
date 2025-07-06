<?php

namespace App\Services\AI;

use App\Services\AbstractServiceProvider;
use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class DeepSeek extends AbstractServiceProvider
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
                ->using(Provider::DeepSeek, 'deepseek-chat')
                ->usingProviderConfig(['api_key' => $apiKey])
                ->withSchema($schema)
                ->withPrompt('Validate this API key by responding with: status="success", provider="DeepSeek", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asStructured();

            $validationData = $response->structured;

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'DeepSeek',
                    message: "DeepSeek API key is valid and working.",
                    code: '200',
                    metadata: [
                        'model' => 'deepseek-chat',
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'DeepSeek',
                    message: 'DeepSeek API key validation failed.',
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
                400 => 'Invalid request format - Please check your input and try again',
                401 => 'Authentication failed - Wrong API key provided',
                402 => 'Insufficient balance - Please check your account balance and top up if needed',
                422 => 'Invalid parameters - Your request contains invalid parameters',
                429 => 'Rate limit reached - You are sending requests too quickly',
                500 => 'Server error - Our server encountered an issue, please retry after a brief wait',
                503 => 'Server overloaded - The server is overloaded due to high traffic, please retry later',
                default => 'An unexpected error occurred while validating the API key',
            };

            $status = match ($statusCode) {
                400, 422 => ValidationStatus::INVALID,
                401 => ValidationStatus::INVALID,
                402 => ValidationStatus::FORBIDDEN,
                429 => ValidationStatus::RATE_LIMITED,
                500, 503 => ValidationStatus::UNAVAILABLE,
                default => ValidationStatus::FAILED,
            };

            return ValidationResult::failure(
                provider: 'DeepSeek',
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