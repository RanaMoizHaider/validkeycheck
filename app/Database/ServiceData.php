<?php

namespace App\Database;

class ServiceData
{
    public static function all(): array
    {
        return [
            [
                'name' => 'OpenAI',
                'slug' => 'openai',
                'category' => 'ai',
                'description' => 'Leading AI research company providing GPT models and other AI services',
                'website_url' => 'https://openai.com',
                'api_keys_url' => 'https://platform.openai.com/api-keys',
                'base_url' => 'https://api.openai.com/v1',
                'documentation_url' => 'https://platform.openai.com/docs/api-reference/authentication',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\OpenAI',
            ],
        ];
    }
} 