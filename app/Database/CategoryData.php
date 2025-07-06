<?php

namespace App\Database;

class CategoryData
{
    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'AI Providers',
                'slug' => 'ai',
                'description' => 'AI and machine learning service providers',
            ],
            [
                'id' => 2,
                'name' => 'Cloud Services',
                'slug' => 'cloud',
                'description' => 'Cloud infrastructure and platform services',
            ],
            [
                'id' => 3,
                'name' => 'Payment Gateways',
                'slug' => 'payment',
                'description' => 'Online payment processing services',
            ],
            [
                'id' => 4,
                'name' => 'Storage Services',
                'slug' => 'storage',
                'description' => 'Cloud storage and database services',
            ],
            [
                'id' => 5,
                'name' => 'Communication Services',
                'slug' => 'communication',
                'description' => 'Email, SMS, and messaging services',
            ],
            [
                'id' => 6,
                'name' => 'Others',
                'slug' => 'other',
                'description' => 'Other API services',
            ],
        ];
    }
} 