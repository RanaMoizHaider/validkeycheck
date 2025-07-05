import * as React from "react"

import { cn } from "@/lib/utils"

function Input({ className, type, ...props }: React.ComponentProps<"input">) {
  return (
    <input
      type={type}
      data-slot="input"
      className={cn(
        "border-gray-200 dark:border-white/20 file:text-foreground placeholder:text-gray-400 dark:placeholder:text-white/40 selection:bg-black dark:selection:bg-white selection:text-white dark:selection:text-black flex h-9 w-full min-w-0 rounded-md border bg-white dark:bg-black px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm text-black dark:text-white",
        "focus-visible:border-black dark:focus-visible:border-white focus-visible:ring-black/20 dark:focus-visible:ring-white/20 focus-visible:ring-[3px]",
        "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
        className
      )}
      {...props}
    />
  )
}

export { Input }
