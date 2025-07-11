import { cn } from '@/lib/utils';
import { Check, Clock, X } from 'lucide-react';
import AppearanceToggleTab from './appearance-tabs';

interface ValidationStats {
    valid: number;
    invalid: number;
    pending: number;
}

interface ValidatorHeaderProps {
    stats: ValidationStats;
    className?: string;
}

export default function ValidatorHeader({ stats, className }: ValidatorHeaderProps) {
    const hasStats = stats.valid > 0 || stats.invalid > 0 || stats.pending > 0;

    return (
        <div className={cn('border-b border-gray-200 px-4 py-3 dark:border-white/10', className)}>
            <div className="mx-auto flex max-w-5xl items-center justify-between">
                <h1 className="text-lg font-medium text-black dark:text-white">Valid Key Check</h1>
                <div className="flex items-center gap-6">
                    {hasStats && (
                        <div className="flex items-center gap-4 text-xs">
                            {stats.valid > 0 && (
                                <span className="flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <Check className="h-3 w-3" />
                                    {stats.valid} valid
                                </span>
                            )}
                            {stats.invalid > 0 && (
                                <span className="flex items-center gap-1 text-red-600 dark:text-red-400">
                                    <X className="h-3 w-3" />
                                    {stats.invalid} invalid
                                </span>
                            )}
                            {stats.pending > 0 && (
                                <span className="flex items-center gap-1 text-gray-600 dark:text-white">
                                    <Clock className="h-3 w-3 animate-spin" />
                                    {stats.pending} testing
                                </span>
                            )}
                        </div>
                    )}
                    <AppearanceToggleTab />
                </div>
            </div>
        </div>
    );
}
