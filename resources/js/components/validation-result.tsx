import { Check, X } from "lucide-react"
import { cn } from "@/lib/utils"

export interface ValidationResultData {
  success: boolean
  message: string
  metadata?: { [key: string]: any }
}

interface ValidationResultProps {
  result: ValidationResultData
  className?: string
}

export default function ValidationResult({ result, className }: ValidationResultProps) {
  return (
    <div
      className={cn(
        "border rounded-lg p-3 transition-colors",
        result.success
          ? "border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20"
          : "border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20",
        className
      )}
    >
      <div className="flex items-center gap-2">
        <div
          className={cn(
            "w-6 h-6 rounded-full flex items-center justify-center",
            result.success
              ? "bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400"
              : "bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-400"
          )}
        >
          {result.success ? <Check className="h-3 w-3" /> : <X className="h-3 w-3" />}
        </div>
        <div>
          <h3
            className={cn(
              "font-medium text-sm",
              result.success
                ? "text-green-900 dark:text-green-100"
                : "text-red-900 dark:text-red-100"
            )}
          >
            {result.success ? "Valid" : "Invalid"}
          </h3>
          <p
            className={cn(
              "text-xs",
              result.success
                ? "text-green-700 dark:text-green-300"
                : "text-red-700 dark:text-red-300"
            )}
          >
            {result.message}
          </p>
        </div>
      </div>

      {result.metadata && Object.keys(result.metadata).length > 0 && (
        <div className="border-t border-gray-200 dark:border-gray-700 pt-3">
          <h4 className="text-xs font-medium text-black dark:text-white mb-2">
            Additional Information
          </h4>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-1 text-xs">
            {Object.entries(result.metadata).map(([key, value]) => (
              <div key={key} className="flex justify-between">
                <span className="text-gray-600 dark:text-white/60">
                  {key.replace(/_/g, ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())}:
                </span>
                <span className="text-black dark:text-white font-medium">
                  {String(value)}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
