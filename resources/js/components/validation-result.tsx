import { Check, X, AlertTriangle, Clock, Ban } from "lucide-react"
import { cn } from "@/lib/utils"
import type { ValidationResult } from "@/types/validator"
import { ValidationStatus } from "@/types/validator"

interface ValidationResultProps {
  result: ValidationResult
  className?: string
}

// Helper function to get the appropriate icon for the status
const getStatusIcon = (status: ValidationStatus) => {
  switch (status) {
    case ValidationStatus.VALID:
      return <Check className="h-4 w-4" />
    case ValidationStatus.INVALID:
      return <X className="h-4 w-4" />
    case ValidationStatus.FORBIDDEN:
      return <Ban className="h-4 w-4" />
    case ValidationStatus.RATE_LIMITED:
      return <Clock className="h-4 w-4" />
    case ValidationStatus.UNAVAILABLE:
      return <AlertTriangle className="h-4 w-4" />
    default:
      return <X className="h-4 w-4" />
  }
}

// Helper function to get the appropriate colors for the status based on the status class
const getStatusColors = (statusClass: string) => {
  switch (statusClass) {
    case 'success':
      return {
        border: 'border-green-200 dark:border-green-800',
        bg: 'bg-green-50 dark:bg-green-900/20',
        iconBg: 'bg-green-100 dark:bg-green-900/40',
        iconColor: 'text-green-800 dark:text-green-400',
        titleColor: 'text-green-900 dark:text-green-100',
        messageColor: 'text-green-700 dark:text-green-300',
        badgeBg: 'bg-green-100 dark:bg-green-900/40',
        badgeColor: 'text-green-800 dark:text-green-400'
      }
    case 'error':
      return {
        border: 'border-red-200 dark:border-red-800',
        bg: 'bg-red-50 dark:bg-red-900/20',
        iconBg: 'bg-red-100 dark:bg-red-900/40',
        iconColor: 'text-red-800 dark:text-red-400',
        titleColor: 'text-red-900 dark:text-red-100',
        messageColor: 'text-red-700 dark:text-red-300',
        badgeBg: 'bg-red-100 dark:bg-red-900/40',
        badgeColor: 'text-red-800 dark:text-red-400'
      }
    case 'warning':
      return {
        border: 'border-orange-200 dark:border-orange-800',
        bg: 'bg-orange-50 dark:bg-orange-900/20',
        iconBg: 'bg-orange-100 dark:bg-orange-900/40',
        iconColor: 'text-orange-800 dark:text-orange-400',
        titleColor: 'text-orange-900 dark:text-orange-100',
        messageColor: 'text-orange-700 dark:text-orange-300',
        badgeBg: 'bg-orange-100 dark:bg-orange-900/40',
        badgeColor: 'text-orange-800 dark:text-orange-400'
      }
    case 'info':
      return {
        border: 'border-blue-200 dark:border-blue-800',
        bg: 'bg-blue-50 dark:bg-blue-900/20',
        iconBg: 'bg-blue-100 dark:bg-blue-900/40',
        iconColor: 'text-blue-800 dark:text-blue-400',
        titleColor: 'text-blue-900 dark:text-blue-100',
        messageColor: 'text-blue-700 dark:text-blue-300',
        badgeBg: 'bg-blue-100 dark:bg-blue-900/40',
        badgeColor: 'text-blue-800 dark:text-blue-400'
      }
    default:
      return {
        border: 'border-gray-200 dark:border-gray-800',
        bg: 'bg-gray-50 dark:bg-gray-900/20',
        iconBg: 'bg-gray-100 dark:bg-gray-900/40',
        iconColor: 'text-gray-800 dark:text-gray-400',
        titleColor: 'text-gray-900 dark:text-gray-100',
        messageColor: 'text-gray-700 dark:text-gray-300',
        badgeBg: 'bg-gray-100 dark:bg-gray-900/40',
        badgeColor: 'text-gray-800 dark:text-gray-400'
      }
  }
}

// Helper function to format values for display
const formatValue = (value: any): string => {
  if (value === null || value === undefined) {
    return 'N/A'
  }
  
  if (typeof value === 'boolean') {
    return value ? 'Yes' : 'No'
  }
  
  if (typeof value === 'number') {
    return value.toLocaleString()
  }
  
  if (Array.isArray(value)) {
    if (value.length === 0) return 'None'
    if (value.length <= 3) return value.join(', ')
    return `${value.slice(0, 2).join(', ')} +${value.length - 2} more`
  }
  
  if (typeof value === 'object') {
    // For objects, show a summary
    const keys = Object.keys(value)
    if (keys.length === 0) return 'Empty'
    if (keys.length <= 2) {
      return keys.map(k => `${k}: ${formatValue(value[k])}`).join(', ')
    }
    return `${keys.length} properties`
  }
  
  if (typeof value === 'string') {
    if (value.length > 50) {
      return value.substring(0, 47) + '...'
    }
    return value
  }
  
  return String(value)
}

// Helper function to format key names
const formatKeyName = (key: string): string => {
  const keyMappings: { [key: string]: string } = {
    'model': 'Model',
    'usage': 'Usage',
    'region': 'Region',
    'user_id': 'User ID',
    'username': 'Username',
    'user_type': 'User Type',
    'public_repos': 'Public Repos',
    'total_tokens': 'Total Tokens',
    'prompt_tokens': 'Prompt Tokens',
    'completion_tokens': 'Completion Tokens'
  }
  
  return keyMappings[key] || key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase()).replace(/_/g, ' ')
}

export default function ValidationResult({ result, className }: ValidationResultProps) {
  // Use the status class from the ValidationResult object
  const colors = getStatusColors(result.status_class)

  return (
    <div
      className={cn(
        "border rounded-lg p-4 transition-colors",
        colors.border,
        colors.bg,
        className
      )}
    >
      <div className="flex items-center justify-between mb-3">
        <div className="flex items-center gap-3">
          <div
            className={cn(
              "w-8 h-8 rounded-full flex items-center justify-center",
              colors.iconBg,
              colors.iconColor
            )}
          >
            {getStatusIcon(result.status)}
          </div>
          <div>
            <h3 className={cn("font-semibold text-base", colors.titleColor)}>
              {result.status_label}
            </h3>
            <p className="text-sm text-gray-600 dark:text-gray-400">
              {result.provider}
            </p>
          </div>
        </div>
        
        {result.code && (
          <div
            className={cn(
              "px-2 py-1 rounded text-xs font-medium",
              colors.badgeBg,
              colors.badgeColor
            )}
          >
            {result.code}
          </div>
        )}
      </div>

      <p className={cn("text-sm leading-relaxed mb-3", colors.messageColor)}>
        {result.message}
      </p>

      {result.metadata && Object.keys(result.metadata).length > 0 && (
        <div className="border-t border-gray-200 dark:border-gray-700 pt-3">
          <h4 className="text-sm font-medium text-black dark:text-white mb-3">
            Additional Information
          </h4>
          <div className="space-y-2">
            {Object.entries(result.metadata).map(([key, value]) => (
              <div key={key} className="flex justify-between items-start">
                <span className="text-sm text-gray-600 dark:text-gray-400 font-medium">
                  {formatKeyName(key)}:
                </span>
                <span className="text-sm text-black dark:text-white text-right max-w-xs">
                  {formatValue(value)}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
