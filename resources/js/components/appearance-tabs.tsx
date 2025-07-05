import { Appearance, useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { LucideIcon, Monitor, Moon, Sun } from 'lucide-react';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

export default function AppearanceToggleTab({ className = '' }: { className?: string }) {
    const { appearance, updateAppearance } = useAppearance();

    const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
        { value: 'light', icon: Sun, label: 'Light' },
        { value: 'dark', icon: Moon, label: 'Dark' },
        { value: 'system', icon: Monitor, label: 'System' },
    ];

    return (
        <Tabs value={appearance} onValueChange={(value) => updateAppearance(value as Appearance)} className={cn(className)}>
            <TabsList className="grid grid-cols-3">
                {tabs.map(({ value, icon: Icon, label }) => (
                    <TabsTrigger key={value} value={value} className="px-2 sm:px-3">
                        <Icon className="h-4 w-4" />
                        <span className="hidden sm:inline ml-2">{label}</span>
                    </TabsTrigger>
                ))}
            </TabsList>
        </Tabs>
    );
}
