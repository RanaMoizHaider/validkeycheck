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
            [
                'name' => 'Anthropic',
                'slug' => 'anthropic',
                'category' => 'ai',
                'description' => 'AI safety company focused on developing safe, beneficial AI systems with Claude models',
                'website_url' => 'https://anthropic.com',
                'api_keys_url' => 'https://console.anthropic.com/settings/keys',
                'base_url' => 'https://api.anthropic.com/v1',
                'documentation_url' => 'https://docs.anthropic.com/en/api/getting-started',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\Anthropic',
            ],
            [
                'name' => 'Amazon Bedrock',
                'slug' => 'amazon-bedrock',
                'category' => 'ai',
                'description' => 'Amazon Bedrock provides access to foundation models from leading AI companies',
                'website_url' => 'https://aws.amazon.com/bedrock/',
                'api_keys_url' => 'https://console.aws.amazon.com/iam/home#/users',
                'base_url' => 'https://bedrock.{region}.amazonaws.com',
                'documentation_url' => 'https://docs.aws.amazon.com/bedrock/latest/userguide/getting-started-api.html',
                'required_fields' => json_encode([
                    'access_key_id' => 'AWS Access Key ID',
                    'secret_access_key' => 'AWS Secret Access Key',
                    'region' => 'AWS Region (e.g., us-east-1)'
                ]),
                'class_name' => 'App\\Services\\AI\\AmazonBedrock',
            ],
            [
                'name' => 'DeepSeek',
                'slug' => 'deepseek',
                'category' => 'ai',
                'description' => 'DeepSeek V3 - Advanced AI model for coding, reasoning, and general conversation',
                'website_url' => 'https://deepseek.com',
                'api_keys_url' => 'https://platform.deepseek.com/api_keys',
                'base_url' => 'https://api.deepseek.com',
                'documentation_url' => 'https://api-docs.deepseek.com',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\DeepSeek',
            ],
        ];
    }
} 