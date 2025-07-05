<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Category extends Model
{
    use Sushi;

    protected $rows = [
        [
            'id' => 1,
            'name' => 'AI Providers',
            'slug' => 'ai-providers',
            'description' => 'AI and machine learning service providers',
        ],
        [
            'id' => 2,
            'name' => 'Cloud Services',
            'slug' => 'cloud-services',
            'description' => 'Cloud infrastructure and platform services',
        ],
        [
            'id' => 3,
            'name' => 'Payment Gateways',
            'slug' => 'payment-gateways',
            'description' => 'Online payment processing services',
        ],
        [
            'id' => 4,
            'name' => 'Others',
            'slug' => 'others',
            'description' => 'Other API services',
        ],
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
