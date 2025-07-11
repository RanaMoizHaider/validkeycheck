<?php

namespace App\Contracts;

use App\Data\ValidationResult;

/**
 * Service Provider Interface
 *
 * Note: Implementations should accept an optional service data object in their constructor
 * for accessing URL configurations from the Service model.
 */
interface ServiceProviderInterface
{
    /**
     * Get the display name of the service provider
     */
    public function getName(): string;

    /**
     * Get the slug identifier for the service provider
     */
    public function getSlug(): string;

    /**
     * Get the category this provider belongs to
     */
    public function getCategory(): string;

    /**
     * Get the description of what this provider does
     */
    public function getDescription(): string;

    /**
     * Get the required fields for validation (e.g., 'api_key', 'secret_key')
     */
    public function getRequiredFields(): array;

    /**
     * Validate the provided API key/credentials
     */
    public function validate(array $credentials): ValidationResult;

    /**
     * Get the main website URL for the service
     */
    public function getWebsiteUrl(): ?string;

    /**
     * Get the URL where users can obtain API keys
     */
    public function getApiKeysUrl(): ?string;

    /**
     * Get the base URL for the service (if applicable)
     */
    public function getBaseUrl(): ?string;

    /**
     * Get the official documentation URL for obtaining API keys
     */
    public function getDocumentationUrl(): ?string;
}
