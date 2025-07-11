import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { Check, Clock, Eye, EyeOff, X } from 'lucide-react';
import { useState } from 'react';

export interface ApiKeyField {
    id: string;
    label: string;
    placeholder: string;
    type: 'text' | 'password';
    required: boolean;
}

export interface ProviderCardData {
    id: string;
    name: string;
    category: string;
    fields: ApiKeyField[];
}

export interface ApiKeyData {
    id: string;
    provider: string;
    fields: { [fieldId: string]: string };
    status: 'valid' | 'invalid' | 'pending' | 'untested';
    lastTested?: Date;
}

interface ProviderCardProps {
    provider: ProviderCardData;
    existingKey?: ApiKeyData;
    onValidate: (providerId: string) => Promise<void>;
    className?: string;
}

export default function ProviderCard({ provider, existingKey, onValidate, className }: ProviderCardProps) {
    const [visibleFields, setVisibleFields] = useState<Set<string>>(new Set());

    const toggleFieldVisibility = (fieldKey: string) => {
        setVisibleFields((prev) => {
            const newSet = new Set(prev);
            if (newSet.has(fieldKey)) {
                newSet.delete(fieldKey);
            } else {
                newSet.add(fieldKey);
            }
            return newSet;
        });
    };

    const getStatusIcon = (status: ApiKeyData['status']) => {
        switch (status) {
            case 'valid':
                return <Check className="h-3 w-3 text-green-600 dark:text-green-400" />;
            case 'invalid':
                return <X className="h-3 w-3 text-red-600 dark:text-red-400" />;
            case 'pending':
                return <Clock className="h-3 w-3 animate-spin text-gray-600 dark:text-white" />;
            default:
                return null;
        }
    };

    const handleValidate = () => {
        onValidate(provider.id);
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            handleValidate();
        }
    };

    return (
        <div
            className={cn(
                'rounded-lg border border-gray-200 bg-white p-3 transition-colors hover:border-gray-300 dark:border-white/10 dark:bg-transparent dark:hover:border-white/20',
                className,
            )}
        >
            <div className="mb-3 flex items-center justify-between">
                <h4 className="text-sm font-medium text-black dark:text-white">{provider.name}</h4>
                {existingKey && (
                    <div className="flex items-center gap-1">
                        {getStatusIcon(existingKey.status)}
                        <span className="text-xs text-gray-600 dark:text-white/60">
                            {existingKey.status === 'pending' ? 'testing' : existingKey.status}
                        </span>
                    </div>
                )}
            </div>

            <div className="space-y-2">
                {provider.fields.map((field) => {
                    const fieldKey = `${provider.id}-${field.id}`;
                    const isVisible = visibleFields.has(fieldKey);

                    return (
                        <div key={field.id}>
                            <Label htmlFor={fieldKey} className="mb-1 block text-xs text-gray-600 dark:text-white/70">
                                {field.label}
                            </Label>
                            <div className="relative">
                                <Input
                                    id={fieldKey}
                                    type={field.type === 'password' && !isVisible ? 'password' : 'text'}
                                    placeholder={field.placeholder}
                                    defaultValue={existingKey?.fields[field.id] || ''}
                                    className="h-7 border-gray-200 bg-white pr-8 text-xs text-black placeholder:text-gray-400 focus:border-black dark:border-white/20 dark:bg-black dark:text-white dark:placeholder:text-white/40 dark:focus:border-white"
                                    onKeyDown={handleKeyDown}
                                />
                                {field.type === 'password' && (
                                    <button
                                        type="button"
                                        onClick={() => toggleFieldVisibility(fieldKey)}
                                        className="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-white/40 dark:hover:text-white/60"
                                    >
                                        {isVisible ? <EyeOff className="h-3 w-3" /> : <Eye className="h-3 w-3" />}
                                    </button>
                                )}
                            </div>
                        </div>
                    );
                })}

                <Button
                    onClick={handleValidate}
                    className="mt-3 h-7 w-full bg-black text-xs font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-white/90"
                    disabled={existingKey?.status === 'pending'}
                >
                    {existingKey?.status === 'pending' ? 'Testing...' : 'Validate'}
                </Button>
            </div>
        </div>
    );
}
