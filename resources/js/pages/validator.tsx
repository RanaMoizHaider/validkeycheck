import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import type { Category, Provider, ValidationResult as ValidationResultType } from '@/types/validator';
import { ValidationStatus } from '@/types/validator';
import ValidatorHeader from '@/components/validator-header';
import ValidatorFooter from '@/components/validator-footer';
import ValidatorHero from '@/components/validator-hero';
import CategoryTabs from '@/components/category-tabs';
import ProviderCard, { ProviderCardData, ApiKeyData, ApiKeyField } from '@/components/provider-card';
import ValidationResult from '@/components/validation-result';
import ThankYouSection from '@/components/thank-you-section';
import { Input } from '@/components/ui/input';

interface ValidatorProps {
  categories: Category[];
  currentYear: number;
  result?: ValidationResultType;
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
        placeholder: ``,
        type: (field.toLowerCase().includes('secret') || field.toLowerCase().includes('key')) ? 'password' as const : 'text' as const,
        required: true,
      }));
    } else {
      // New format: object with field_name => display_label
      fields = Object.entries(provider.required_fields).map(([fieldName, displayLabel]) => ({
        id: fieldName,
        label: displayLabel,
        placeholder: ``,
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



export default function Validator({ categories, currentYear, result }: ValidatorProps) {
  const [activeCategory, setActiveCategory] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [apiKeys, setApiKeys] = useState<ApiKeyData[]>([]);
  const [validationResult, setValidationResult] = useState<ValidationResultType | null>(null);
  const [testingProvider, setTestingProvider] = useState<string | null>(null);

  // Handle result from backend (if any)
  useEffect(() => {
    if (result) {
      setTestingProvider(null);
      setValidationResult({
        success: result.success,
        status: result.status || (result.success ? ValidationStatus.VALID : ValidationStatus.INVALID),
        message: result.message,
        provider: result.provider || 'Unknown',
        code: result.code,
        metadata: result.metadata,
        status_class: result.status_class || (result.success ? 'success' : 'error'),
        status_label: result.status_label || (result.success ? 'Valid' : 'Invalid')
      });
    }
  }, [result]);

  // Get all providers from all categories and convert them
  const allProviders = categories.flatMap(cat => cat.providers);
  const providerCards = convertToProviderCardData(allProviders);
  
  // Get unique category names and add "All" at the beginning
  const categoryNames = ['All', ...new Set(categories.map(cat => cat.name))];
  
  // Filter providers based on search query
  const filteredProviders = providerCards.filter(provider => 
    provider.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    provider.category.toLowerCase().includes(searchQuery.toLowerCase())
  );
  
  // Get providers for the active category and sort alphabetically
  const activeProviders = (activeCategory === 'all' 
    ? filteredProviders
    : filteredProviders.filter(provider => {
        const category = categories.find(cat => cat.name === activeCategory);
        return category?.providers.some(p => p.slug === provider.id);
      })).sort((a, b) => a.name.localeCompare(b.name));

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
        status: ValidationStatus.UNAVAILABLE,
        message: `Please fill in all required fields: ${missingFields.join(', ')}`,
        provider: provider.name,
        code: 'MISSING FIELDS',
        metadata: { missing_fields: missingFields },
        status_class: 'error',
        status_label: 'Invalid'
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
          status: data.status as ValidationStatus || (data.success ? ValidationStatus.VALID : ValidationStatus.INVALID),
          message: data.message,
          provider: data.provider || provider.name,
          code: data.code,
          metadata: data.metadata,
          status_class: data.status_class || (data.success ? 'success' : 'error'),
          status_label: data.status_label || (data.success ? 'Valid' : 'Invalid')
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
        status: ValidationStatus.INVALID,
        message: error instanceof Error ? error.message : 'An error occurred during validation',
        provider: provider.name,
        code: 'VALIDATION_ERROR',
        metadata: { error: error instanceof Error ? error.message : 'Unknown error' },
        status_class: 'error',
        status_label: 'Invalid'
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
      
      <div className="min-h-screen bg-white dark:bg-black text-black dark:text-white flex flex-col">
        <ValidatorHeader stats={stats} className="sticky top-0 z-50 bg-white dark:bg-black" />

        <div className="flex-1 max-w-5xl mx-auto px-4 py-6 w-full">
          <ValidatorHero />

          {/* Search Bar */}
          <div className="mb-6">
            <div className="relative max-w-md mx-auto">
              <Input
                type="text"
                placeholder="Search services..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg className="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            {searchQuery && (
              <div className="text-center mt-2 text-sm text-gray-600 dark:text-gray-400">
                {activeProviders.length} service{activeProviders.length !== 1 ? 's' : ''} found
              </div>
            )}
          </div>

          <CategoryTabs
            categories={categoryNames}
            activeCategory={activeCategory === 'all' ? 'All' : activeCategory}
            onCategoryChange={(categoryName) => {
              if (categoryName === 'All') {
                setActiveCategory('all');
              } else {
                setActiveCategory(categoryName);
              }
            }}
            className="mb-6"
          >
            <div className="columns-1 md:columns-2 lg:columns-3 gap-4">
              {activeProviders.length > 0 ? (
                activeProviders.map((provider) => {
                  const existingKey = apiKeys.find(k => k.provider === provider.id);
                  return (
                    <ProviderCard
                      key={provider.id}
                      provider={provider}
                      existingKey={existingKey}
                      onValidate={handleValidation}
                      className="break-inside-avoid mb-4"
                    />
                  );
                })
              ) : (
                <div className="col-span-full text-center py-12">
                  <div className="text-gray-500 dark:text-gray-400">
                    {searchQuery ? (
                      <>
                        <svg className="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p className="text-lg font-medium">No services found</p>
                        <p className="text-sm">Try adjusting your search terms</p>
                      </>
                    ) : (
                      <>
                        <p className="text-lg font-medium">No services available</p>
                        <p className="text-sm">Check back later for more services</p>
                      </>
                    )}
                  </div>
                </div>
              )}
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

          {/* Credits Section */}
          <ThankYouSection />
        </div>

        <ValidatorFooter currentYear={currentYear} className="sticky bottom-0 z-50 bg-white dark:bg-black" />
      </div>
    </>
  );
}
