<?php

namespace App\Database;

class CategoryData
{
    public static function all(): array
    {
        return [
            [
                'name' => 'AI Providers',
                'slug' => 'ai',
                'description' => 'AI and machine learning service providers',
            ],
            [
                'name' => 'Cloud Services',
                'slug' => 'cloud',
                'description' => 'Cloud infrastructure and platform services',
            ],
            [
                'name' => 'Payment Gateways',
                'slug' => 'payment',
                'description' => 'Online payment processing services',
            ],
            [
                'name' => 'Storage Services',
                'slug' => 'storage',
                'description' => 'Cloud storage and database services',
            ],
            [
                'name' => 'Communication Services',
                'slug' => 'communication',
                'description' => 'Email, SMS, and messaging services',
            ],
            [
                'name' => 'Others',
                'slug' => 'other',
                'description' => 'Other API services',
            ],
        ];
    }
}
