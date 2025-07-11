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

class Anthropic extends AbstractServiceProvider
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
                ->using(Provider::Anthropic, 'claude-3-haiku-20240307')
                ->usingProviderConfig(['api_key' => $apiKey])
                ->withSchema($schema)
                ->withPrompt('Validate this API key by responding with: status="success", provider="Anthropic", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asStructured();

            $validationData = $response->structured;

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'Anthropic',
                    message: "Anthropic API key is valid and working.",
                    code: '200',
                    metadata: [
                        'model' => 'claude-3-haiku-20240307',
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'Anthropic',
                    message: 'Anthropic API key validation failed.',
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
                400 => 'Invalid request - There was an issue with the format or content of your request',
                401 => 'Authentication error - There\'s an issue with your API key',
                403 => 'Permission error - Your API key does not have permission to use the specified resource',
                404 => 'Not found - The requested resource was not found',
                413 => 'Request too large - Request exceeds the maximum allowed number of bytes',
                429 => 'Rate limit error - Your account has hit a rate limit',
                500 => 'API error - An unexpected error has occurred internal to Anthropic\'s systems',
                529 => 'Overloaded error - Anthropic\'s API is temporarily overloaded',
                default => 'Anthropic API request failed',
            };

            $status = match ($statusCode) {
                429 => ValidationStatus::RATE_LIMITED,
                500, 529 => ValidationStatus::UNAVAILABLE,
                default => ValidationStatus::INVALID,
            };

            return ValidationResult::failure(
                provider: 'Anthropic',
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
