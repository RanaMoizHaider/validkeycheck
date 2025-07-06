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

class OpenAI extends AbstractServiceProvider
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
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->usingProviderConfig([
                    'api_key' => $apiKey,
                ])
                ->withSchema($schema)
                ->withPrompt('Validate this API key by responding with: status="success", provider="OpenAI", is_valid=true, message="API key is valid and working"')
                ->withClientOptions([
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ])
                ->asStructured();

            $validationData = $response->structured;

            if ($validationData && $validationData['is_valid']) {
                return ValidationResult::success(
                    provider: 'OpenAI',
                    message: "OpenAI API key is valid and working.",
                    code: '200',
                    metadata: [
                        'model' => 'gpt-4o-mini',
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                        'finish_reason' => $response->finishReason->name,
                    ]
                );
            } else {
                return ValidationResult::failure(
                    provider: 'OpenAI',
                    message: 'OpenAI API key validation failed.',
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
            
            switch ($statusCode) {
                case 400:
                    $userMessage = 'Invalid request format or missing parameters';
                    break;
                case 401:
                    $userMessage = 'Invalid API key provided';
                    break;
                case 403:
                    $userMessage = 'Access forbidden - check your API key permissions';
                    break;
                case 404:
                    $userMessage = 'Requested resource not found';
                    break;
                case 429:
                    $userMessage = 'Rate limit exceeded - please try again later';
                    break;
                case 500:
                    $userMessage = 'OpenAI service temporarily unavailable';
                    break;
                case 503:
                    $userMessage = 'OpenAI service is currently overloaded - please try again later';
                    break;
                default:
                    $userMessage = 'OpenAI API request failed';
            }

            return ValidationResult::failure(
                provider: 'OpenAI',
                message: $userMessage,
                status: ValidationStatus::INVALID,
                code: $statusCode
            );
        } catch (\Exception $e) {
            return ValidationResult::failure(
                provider: 'OpenAI',
                message: 'An unexpected error occurred',
                status: ValidationStatus::FAILED,
                code: null,
                metadata: [
                    'details' => $e->getMessage(),
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
