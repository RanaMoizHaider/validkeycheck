import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs"
import { cn } from "@/lib/utils"

interface CategoryTabsProps {
  categories: string[]
  activeCategory: string
  onCategoryChange: (category: string) => void
  children: React.ReactNode
  className?: string
}

export default function CategoryTabs({ 
  categories, 
  activeCategory, 
  onCategoryChange, 
  children, 
  className 
}: CategoryTabsProps) {
  return (
    <Tabs value={activeCategory} onValueChange={onCategoryChange} className={cn("w-full", className)}>
      <div className="flex justify-center mb-6">
        <div className="overflow-x-auto w-full max-w-4xl">
          <TabsList className="flex w-max mx-auto">
            {categories.map((category) => (
              <TabsTrigger
                key={category}
                value={category}
                className="text-sm py-2 px-4 whitespace-nowrap"
              >
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
  )
}
