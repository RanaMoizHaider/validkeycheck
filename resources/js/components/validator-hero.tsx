import { cn } from "@/lib/utils"

interface ValidatorHeroProps {
  className?: string
}

export default function ValidatorHero({ className }: ValidatorHeroProps) {
  return (
    <div className={cn("text-center mb-6", className)}>
      <h2 className="text-2xl font-light mb-2 text-black dark:text-white">
        Validate your API keys
      </h2>
      <p className="text-gray-600 dark:text-white/60 text-sm">
        Test credentials across multiple providers
      </p>
    </div>
  )
}
