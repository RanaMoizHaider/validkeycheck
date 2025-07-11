import { cn } from '@/lib/utils';

interface ValidatorHeroProps {
    className?: string;
}

export default function ValidatorHero({ className }: ValidatorHeroProps) {
    return (
        <div className={cn('mb-6 text-center', className)}>
            <h2 className="mb-2 text-2xl font-light text-black dark:text-white">Validate your API keys</h2>
            <p className="text-sm text-gray-600 dark:text-white/60">Test credentials across multiple providers</p>
        </div>
    );
}
