<?php

namespace App\Data;

use App\Enums\ValidationStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ValidationResult extends Data
{
    public function __construct(
        public readonly bool $success,
        public readonly string $provider,
        public readonly string $message,
        public readonly ValidationStatus $status,
        public readonly string|Optional $code = new Optional,
        public readonly array|Optional $metadata = new Optional,
    ) {}

    /**
     * Create a successful validation result
     */
    public static function success(
        string $provider,
        string $message,
        ?string $code = null,
        ?array $metadata = null
    ): self {
        return new self(
            success: true,
            provider: $provider,
            message: $message,
            status: ValidationStatus::VALID,
            code: $code ?? new Optional,
            metadata: $metadata ?? new Optional
        );
    }

    /**
     * Create a failed validation result
     */
    public static function failure(
        string $provider,
        string $message,
        ?ValidationStatus $status = null,
        ?string $code = null,
        ?array $metadata = null
    ): self {
        return new self(
            success: false,
            provider: $provider,
            message: $message,
            status: $status ?? ValidationStatus::INVALID,
            code: $code ?? new Optional,
            metadata: $metadata ?? new Optional
        );
    }

    /**
     * Get the CSS class for the status
     */
    public function getStatusClass(): string
    {
        return $this->status->getStatusClass();
    }

    /**
     * Get the display label for the status
     */
    public function getStatusLabel(): string
    {
        return $this->status->getStatusLabel();
    }

    /**
     * Transform the data to include computed properties
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['status'] = $this->status->value;
        $data['status_class'] = $this->getStatusClass();
        $data['status_label'] = $this->getStatusLabel();

        return $data;
    }
}
