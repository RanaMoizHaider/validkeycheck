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
            [
                'name' => 'Google Gemini',
                'slug' => 'gemini',
                'category' => 'ai',
                'description' => 'Google\'s most capable AI model for text, code, and multimodal understanding',
                'website_url' => 'https://ai.google.dev',
                'api_keys_url' => 'https://aistudio.google.com/app/apikey',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'documentation_url' => 'https://ai.google.dev/gemini-api/docs/api-key',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\Gemini',
            ],
            [
                'name' => 'OpenRouter',
                'slug' => 'openrouter',
                'category' => 'ai',
                'description' => 'Universal API for accessing multiple AI models from different providers',
                'website_url' => 'https://openrouter.ai',
                'api_keys_url' => 'https://openrouter.ai/keys',
                'base_url' => 'https://openrouter.ai/api/v1',
                'documentation_url' => 'https://openrouter.ai/docs/api-reference/authentication',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\OpenRouter',
            ],
            [
                'name' => 'Groq',
                'slug' => 'groq',
                'category' => 'ai',
                'description' => 'Ultra-fast AI inference platform with Lightning-fast LLM API',
                'website_url' => 'https://groq.com',
                'api_keys_url' => 'https://console.groq.com/keys',
                'base_url' => 'https://api.groq.com/openai/v1',
                'documentation_url' => 'https://console.groq.com/docs/quickstart',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\Groq',
            ],
            [
                'name' => 'Mistral AI',
                'slug' => 'mistral',
                'category' => 'ai',
                'description' => 'Advanced AI models for chat, code generation, and reasoning tasks',
                'website_url' => 'https://mistral.ai',
                'api_keys_url' => 'https://console.mistral.ai/api-keys',
                'base_url' => 'https://api.mistral.ai/v1',
                'documentation_url' => 'https://docs.mistral.ai/api/',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\Mistral',
            ],
            [
                'name' => 'xAI',
                'slug' => 'xai',
                'category' => 'ai',
                'description' => 'Elon Musk\'s AI company developing Grok and other AI technologies',
                'website_url' => 'https://x.ai',
                'api_keys_url' => 'https://console.x.ai/team/api-keys',
                'base_url' => 'https://api.x.ai/v1',
                'documentation_url' => 'https://docs.x.ai/api',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\XAI',
            ],
            [
                'name' => 'Perplexity',
                'slug' => 'perplexity',
                'category' => 'ai',
                'description' => 'Perplexity AI for real-time web search and Q&A capabilities',
                'website_url' => 'https://www.perplexity.ai',
                'api_keys_url' => 'https://www.perplexity.ai/settings/api',
                'base_url' => 'https://api.perplexity.ai/v1',
                'documentation_url' => 'https://docs.perplexity.ai/',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\Perplexity',
            ],
            [
                'name' => 'Exa',
                'slug' => 'exa',
                'category' => 'ai',
                'description' => 'Exa provides AI-powered search and answer generation with citations',
                'website_url' => 'https://exa.ai',
                'api_keys_url' => 'https://dashboard.exa.ai/api-keys',
                'base_url' => 'https://api.exa.ai',
                'documentation_url' => 'https://docs.exa.ai',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\AI\\Exa',
            ],
            [
                'name' => 'Polar',
                'slug' => 'polar',
                'category' => 'payment',
                'description' => 'Online payment processing platform for internet businesses',
                'website_url' => 'https://polar.sh',
                'api_keys_url' => 'https://polar.sh',
                'base_url' => 'https://api.polar.sh/v1',
                'documentation_url' => 'https://docs.polar.sh',
                'required_fields' => json_encode([
                    'api_key' => 'API Key'
                ]),
                'class_name' => 'App\\Services\\Payment\\Polar',
            ],
        ];
    }
} 