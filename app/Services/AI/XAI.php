<?php

namespace App\Services\AI;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;

class XAI extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $apiKey = $credentials['api_key'];

        try {
            $response = Prism::text()
                ->using(Provider::XAI, 'grok-3-mini')
                ->usingProviderConfig(['api_key' => $apiKey])
                ->withSystemPrompt('You are a validation service. Respond with a valid JSON object containing the validation result. Do not include any other text in your response.')
                ->withPrompt('Validate this API key by responding with a JSON object containing: status="success", provider="xAI", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asText();

            // Parse the text response to extract validation data
            $validationData = null;
            try {
                // Try to parse the response as JSON
                $jsonResponse = json_decode($response->text, true);

                // Check if we have a valid JSON response with the required fields
                if (is_array($jsonResponse) &&
                    isset($jsonResponse['status']) &&
                    isset($jsonResponse['provider']) &&
                    isset($jsonResponse['is_valid']) &&
                    isset($jsonResponse['message'])) {
                    $validationData = $jsonResponse;
                }
            } catch (\Throwable $e) {
                // If JSON parsing fails, we'll handle it as a failed validation
            }

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'xAI',
                    message: 'xAI API key is valid and working.',
                    code: '200',
                    metadata: [
                        'model' => 'grok-beta',
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'xAI',
                    message: 'xAI API key validation failed.',
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
                400, 422, 401, 403, 404 => ValidationStatus::INVALID,
                429 => ValidationStatus::RATE_LIMITED,
                500, 502, 503, 504 => ValidationStatus::UNAVAILABLE,
                default => ValidationStatus::FAILED,
            };

            return ValidationResult::failure(
                provider: 'xAI',
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
