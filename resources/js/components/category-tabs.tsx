import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { cn } from '@/lib/utils';

interface CategoryTabsProps {
    categories: string[];
    activeCategory: string;
    onCategoryChange: (category: string) => void;
    children: React.ReactNode;
    className?: string;
}

export default function CategoryTabs({ categories, activeCategory, onCategoryChange, children, className }: CategoryTabsProps) {
    return (
        <Tabs value={activeCategory} onValueChange={onCategoryChange} className={cn('w-full', className)}>
            <div className="mb-6 flex justify-center">
                <div className="scrollbar-hide w-full max-w-4xl overflow-x-auto">
                    <TabsList className="mx-auto flex w-max">
                        {categories.map((category) => (
                            <TabsTrigger key={category} value={category} className="px-4 py-2 text-sm whitespace-nowrap">
                                {category}
                            </TabsTrigger>
                        ))}
                    </TabsList>
                </div>
            </div>

            {categories.map((category) => (
                <TabsContent key={category} value={category} className="mt-0">
                    {children}
                </TabsContent>
            ))}
        </Tabs>
    );
}
