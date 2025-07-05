import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { Category, Provider } from '@/types/validator';
import ValidatorHeader from '@/components/validator-header';
import ValidatorHero from '@/components/validator-hero';
import CategoryTabs from '@/components/category-tabs';
import ProviderCard, { ProviderCardData, ApiKeyData, ApiKeyField } from '@/components/provider-card';
import ValidationResult, { ValidationResultData } from '@/components/validation-result';

interface ValidatorProps {
  categories: Category[];
  result?: {
    success: boolean;
    message: string;
    provider?: string;
    metadata?: any;
  };
}

// Convert backend providers to frontend format
const convertToProviderCardData = (providers: Provider[]): ProviderCardData[] => {
  return providers.map(provider => {
    let fields: ApiKeyField[] = [];
    
    if (Array.isArray(provider.required_fields)) {
      // Old format: array of field names
      fields = provider.required_fields.map(field => ({
        id: field,
        label: field.replace(/_/g, ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()),
        placeholder: `Enter your ${field.replace(/_/g, ' ')}`,
        type: (field.toLowerCase().includes('secret') || field.toLowerCase().includes('key')) ? 'password' as const : 'text' as const,
        required: true,
      }));
    } else {
      // New format: object with field_name => display_label
      fields = Object.entries(provider.required_fields).map(([fieldName, displayLabel]) => ({
        id: fieldName,
        label: displayLabel,
        placeholder: `Enter your ${displayLabel.toLowerCase()}`,
        type: (fieldName.toLowerCase().includes('secret') || fieldName.toLowerCase().includes('key') || fieldName.toLowerCase().includes('token')) ? 'password' as const : 'text' as const,
        required: true,
      }));
    }

    return {
      id: provider.slug,
      name: provider.name,
      category: provider.category,
      fields,
    };
  });
};



export default function Validator({ categories, result }: ValidatorProps) {
  const [activeCategory, setActiveCategory] = useState<string>(categories[0]?.slug || '');
  const [apiKeys, setApiKeys] = useState<ApiKeyData[]>([]);
  const [validationResult, setValidationResult] = useState<ValidationResultData | null>(null);
  const [testingProvider, setTestingProvider] = useState<string | null>(null);

  // Handle result from backend (if any)
  useEffect(() => {
    if (result) {
      setTestingProvider(null);
      setValidationResult({
        success: result.success,
        message: result.message,
        metadata: result.metadata
      });
    }
  }, [result]);

  // Get all providers from all categories and convert them
  const allProviders = categories.flatMap(cat => cat.providers);
  const providerCards = convertToProviderCardData(allProviders);
  
  // Get unique category names
  const categoryNames = [...new Set(categories.map(cat => cat.name))];
  
  // Get providers for the active category
  const activeProviders = providerCards.filter(provider => {
    const category = categories.find(cat => cat.slug === activeCategory);
    return category?.providers.some(p => p.slug === provider.id);
  });

  const handleValidation = async (providerId: string): Promise<void> => {
    const provider = providerCards.find(p => p.id === providerId);
    if (!provider) return;

    const fields: { [fieldId: string]: string } = {};
    const missingFields: string[] = [];

    // Collect field values and check for required fields
    provider.fields.forEach((field) => {
      const input = document.getElementById(`${providerId}-${field.id}`) as HTMLInputElement;
      const value = input?.value?.trim() || "";
      fields[field.id] = value;
      
      if (field.required && !value) {
        missingFields.push(field.label);
      }
    });

    // Check if any required fields are missing
    if (missingFields.length > 0) {
      setValidationResult({
        success: false,
        message: `Please fill in all required fields: ${missingFields.join(', ')}`,
        metadata: undefined
      });
      return;
    }

    const keyId = `${providerId}-${Date.now()}`;
    const newKey: ApiKeyData = {
      id: keyId,
      provider: providerId,
      fields,
      status: "pending",
    };

    setApiKeys(prev => [...prev.filter(k => k.provider !== providerId), newKey]);
    setValidationResult(null);
    setTestingProvider(providerId);

    try {
      // Make direct API call instead of using Inertia
      const response = await fetch('/validate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          provider: providerId,
          credentials: fields
        })
      });

      const data = await response.json();

      if (response.ok) {
        setValidationResult({
          success: data.success,
          message: data.message,
          metadata: data.metadata
        });

        // Update the API key status
        const newStatus = data.success ? 'valid' : 'invalid';
        setApiKeys(prev => prev.map(key => 
          key.provider === providerId 
            ? { ...key, status: newStatus }
            : key
        ));
      } else {
        throw new Error(data.message || 'Validation failed');
      }
    } catch (error) {
      console.error('Validation error:', error);
      setValidationResult({
        success: false,
        message: error instanceof Error ? error.message : 'An error occurred during validation',
        metadata: undefined
      });

      setApiKeys(prev => prev.map(key => 
        key.provider === providerId 
          ? { ...key, status: 'invalid' }
          : key
      ));
    } finally {
      setTestingProvider(null);
    }
  };

  const stats = {
    valid: apiKeys.filter((k: ApiKeyData) => k.status === "valid").length,
    invalid: apiKeys.filter((k: ApiKeyData) => k.status === "invalid").length,
    pending: apiKeys.filter((k: ApiKeyData) => k.status === "pending").length,
  };

  return (
    <>
      <Head title="API Key Validator" />
      
      <div className="min-h-screen bg-white dark:bg-black text-black dark:text-white">
        <ValidatorHeader stats={stats} />

        <div className="max-w-5xl mx-auto px-4 py-6">
          <ValidatorHero />

          <CategoryTabs
            categories={categoryNames}
            activeCategory={categories.find(cat => cat.slug === activeCategory)?.name || categoryNames[0]}
            onCategoryChange={(categoryName) => {
              const category = categories.find(cat => cat.name === categoryName);
              if (category) setActiveCategory(category.slug);
            }}
            className="mb-6"
          >
            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3 auto-rows-fr">
              {activeProviders.map((provider) => {
                const existingKey = apiKeys.find(k => k.provider === provider.id);
                return (
                  <ProviderCard
                    key={provider.id}
                    provider={provider}
                    existingKey={existingKey}
                    onValidate={handleValidation}
                  />
                );
              })}
            </div>
          </CategoryTabs>

          {/* Testing State */}
          {testingProvider && (
            <div className="mb-6">
              <div className="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <div className="flex items-center space-x-3">
                  <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                  <div>
                    <h3 className="text-lg font-medium text-black dark:text-white">
                      Testing {providerCards.find(p => p.id === testingProvider)?.name}
                    </h3>
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                      Validating your API key...
                    </p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Validation Result */}
          {validationResult && !testingProvider && (
            <div>
              <h3 className="text-lg font-medium text-black dark:text-white mb-4">
                Validation Result
              </h3>
              <ValidationResult result={validationResult} />
            </div>
          )}
        </div>
      </div>
    </>
  );
}
