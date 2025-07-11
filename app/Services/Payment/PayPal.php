<?php

namespace App\Services\Payment;

use App\Data\ValidationResult;
use App\Enums\ValidationStatus;
use App\Services\AbstractServiceProvider;

class PayPal extends AbstractServiceProvider
{
    protected function performValidation(array $credentials): ValidationResult
    {
        $clientId = $credentials['client_id'];
        $clientSecret = $credentials['client_secret'];

        try {
            $response = $this->makeHttpRequest(
                'https://api-m.paypal.com/v1/oauth2/token',
                [
                    'method' => 'POST',
                    'headers' => [
                        'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'data' => [
                        'grant_type' => 'client_credentials',
                    ],
                ]
            );

            $statusCode = $response['status_code'];
            $data = $response['data'];

            if ($statusCode === 200) {
                $accessToken = $data['access_token'] ?? null;
                $tokenType = $data['token_type'] ?? null;
                $expiresIn = $data['expires_in'] ?? null;
                $appId = $data['app_id'] ?? null;
                $nonce = $data['nonce'] ?? null;

                return ValidationResult::success(
                    provider: $this->getName(),
                    message: 'PayPal credentials are valid and working',
                    metadata: [
                        'access_token' => $accessToken,
                        'token_type' => $tokenType,
                        'expires_in' => $expiresIn,
                        'app_id' => $appId,
                        'nonce' => $nonce,
                        'environment' => 'production',
                        'scope' => $data['scope'] ?? null,
                    ]
                );
            }

            $userMessage = match ($statusCode) {
                400 => 'Invalid request parameters',
                401 => 'Invalid client credentials - authentication failed',
                403 => 'Authorization failed due to insufficient permissions',
                404 => 'Resource not found',
                405 => 'Method not allowed',
                406 => 'Media type not acceptable',
                409 => 'Resource conflict',
                415 => 'Unsupported media type',
                422 => 'Unprocessable entity',
                429 => 'Too many requests - rate limit exceeded',
                500 => 'Internal server error',
                503 => 'Service unavailable',
                default => "Unexpected response from PayPal API (Status: {$statusCode})",
            };

            $status = match ($statusCode) {
                400, 401, 404, 405, 406, 415, 422 => ValidationStatus::INVALID,
                403, 409 => ValidationStatus::FORBIDDEN,
                429 => ValidationStatus::RATE_LIMITED,
                500, 503 => ValidationStatus::UNAVAILABLE,
                default => ValidationStatus::FAILED,
            };

            return ValidationResult::failure(
                provider: $this->getName(),
                message: $userMessage,
                status: $status,
                code: $statusCode,
                metadata: [
                    'status_code' => $statusCode,
                    'error' => $data['error'] ?? null,
                    'error_description' => $data['error_description'] ?? null,
                ]
            );
        } catch (\Exception $e) {
            return ValidationResult::failure(
                provider: $this->getName(),
                message: 'Failed to connect to PayPal API: '.$e->getMessage(),
                status: ValidationStatus::FAILED,
                metadata: [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    public function getRequiredFields(): array
    {
        return [
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret',
        ];
    }
}
