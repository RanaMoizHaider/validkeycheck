<?php

namespace App\Enums;

enum ValidationStatus: string
{
    case VALID = 'valid';
    case INVALID = 'invalid';
    case FORBIDDEN = 'forbidden';
    case RATE_LIMITED = 'rate_limited';
    case UNAVAILABLE = 'unavailable';
    case FAILED = 'failed';

    public function getStatusClass(): string
    {
        return match ($this) {
            self::VALID => 'success',
            self::INVALID => 'error',
            self::FORBIDDEN => 'warning',
            self::RATE_LIMITED => 'warning',
            self::UNAVAILABLE => 'info',
            self::FAILED => 'error',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this) {
            self::VALID => 'Valid',
            self::INVALID => 'Invalid',
            self::FORBIDDEN => 'Forbidden',
            self::RATE_LIMITED => 'Rate Limited',
            self::UNAVAILABLE => 'Unavailable',
            self::FAILED => 'Failed',
        };
    }
}
