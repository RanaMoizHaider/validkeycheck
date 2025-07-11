<?php

namespace App\Services\AI;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;
use Prism\Bedrock\Bedrock;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class AmazonBedrock extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $accessKeyId = $credentials['access_key_id'];
        $secretAccessKey = $credentials['secret_access_key'];
        $region = $credentials['region'];

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
                ->using(Bedrock::KEY, 'anthropic.claude-3-haiku-20240307-v1:0')
                ->usingProviderConfig([
                    'region' => $region,
                    'api_key' => $accessKeyId,
                    'api_secret' => $secretAccessKey,
                ])
                ->withSchema($schema)
                ->withPrompt('Validate this API key by responding with: status="success", provider="Amazon Bedrock", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asStructured();

            $validationData = $response->structured;

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'Amazon Bedrock',
                    message: 'Amazon Bedrock credentials are valid and working.',
                    code: '200',
                    metadata: [
                        'model' => 'anthropic.claude-3-haiku-20240307-v1:0',
                        'region' => $region,
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'Amazon Bedrock',
                    message: 'Amazon Bedrock credentials validation failed.',
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
                400 => 'Validation error - The input fails to satisfy the constraints specified by Amazon Bedrock',
                403 => 'Access denied - You do not have sufficient permissions to perform this action',
                404 => 'Resource not found - The requested resource could not be found',
                408 => 'Request timeout - The request took too long to process',
                424 => 'Model stream error - An error occurred while streaming the response',
                429 => 'Throttling - Request was denied due to exceeding account quotas',
                500 => 'Internal server error - The request processing failed due to a server error',
                503 => 'Service unavailable - The service is temporarily unable to handle the request',
                default => 'An unexpected error occurred while validating the API key',
            };

            $status = match ($statusCode) {
                400, 404 => ValidationStatus::INVALID,
                403 => ValidationStatus::FORBIDDEN,
                408, 500, 503 => ValidationStatus::UNAVAILABLE,
                429 => ValidationStatus::RATE_LIMITED,
                default => ValidationStatus::FAILED,
            };

            return ValidationResult::failure(
                provider: 'Amazon Bedrock',
                message: $userMessage,
                status: $status,
                code: $statusCode
            );
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'access_key_id' => 'AWS Access Key ID',
            'secret_access_key' => 'AWS Secret Access Key',
            'region' => 'AWS Region (e.g., us-east-1)',
        ];
    }
}
