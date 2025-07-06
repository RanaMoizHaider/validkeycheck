import { useState } from "react"
import { Eye, EyeOff, Check, X, Clock } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { cn } from "@/lib/utils"

export interface ApiKeyField {
  id: string
  label: string
  placeholder: string
  type: "text" | "password"
  required: boolean
}

export interface ProviderCardData {
  id: string
  name: string
  category: string
  fields: ApiKeyField[]
}

export interface ApiKeyData {
  id: string
  provider: string
  fields: { [fieldId: string]: string }
  status: "valid" | "invalid" | "pending" | "untested"
  lastTested?: Date
}

interface ProviderCardProps {
  provider: ProviderCardData
  existingKey?: ApiKeyData
  onValidate: (providerId: string) => Promise<void>
  className?: string
}

export default function ProviderCard({ 
  provider, 
  existingKey, 
  onValidate, 
  className 
}: ProviderCardProps) {
  const [visibleFields, setVisibleFields] = useState<Set<string>>(new Set())

  const toggleFieldVisibility = (fieldKey: string) => {
    setVisibleFields((prev) => {
      const newSet = new Set(prev)
      if (newSet.has(fieldKey)) {
        newSet.delete(fieldKey)
      } else {
        newSet.add(fieldKey)
      }
      return newSet
    })
  }

  const getStatusIcon = (status: ApiKeyData["status"]) => {
    switch (status) {
      case "valid":
        return <Check className="h-3 w-3 text-green-600 dark:text-green-400" />
      case "invalid":
        return <X className="h-3 w-3 text-red-600 dark:text-red-400" />
      case "pending":
        return <Clock className="h-3 w-3 text-gray-600 dark:text-white animate-spin" />
      default:
        return null
    }
  }

  const handleValidate = () => {
    onValidate(provider.id)
  }

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === "Enter") {
      handleValidate()
    }
  }

  return (
    <div
      className={cn(
        "border border-gray-200 dark:border-white/10 rounded-lg p-3 hover:border-gray-300 dark:hover:border-white/20 transition-colors bg-white dark:bg-transparent",
        className
      )}
    >
      <div className="flex items-center justify-between mb-3">
        <h4 className="font-medium text-sm text-black dark:text-white">{provider.name}</h4>
        {existingKey && (
          <div className="flex items-center gap-1">
            {getStatusIcon(existingKey.status)}
            <span className="text-xs text-gray-600 dark:text-white/60">
              {existingKey.status === "pending" ? "testing" : existingKey.status}
            </span>
          </div>
        )}
      </div>

      <div className="space-y-2">
        {provider.fields.map((field) => {
          const fieldKey = `${provider.id}-${field.id}`
          const isVisible = visibleFields.has(fieldKey)

          return (
            <div key={field.id}>
              <Label htmlFor={fieldKey} className="text-xs text-gray-600 dark:text-white/70 mb-1 block">
                {field.label}
              </Label>
              <div className="relative">
                <Input
                  id={fieldKey}
                  type={field.type === "password" && !isVisible ? "password" : "text"}
                  placeholder={field.placeholder}
                  defaultValue={existingKey?.fields[field.id] || ""}
                  className="bg-white dark:bg-black border-gray-200 dark:border-white/20 text-black dark:text-white text-xs h-7 pr-8 placeholder:text-gray-400 dark:placeholder:text-white/40 focus:border-black dark:focus:border-white"
                  onKeyDown={handleKeyDown}
                />
                {field.type === "password" && (
                  <button
                    type="button"
                    onClick={() => toggleFieldVisibility(fieldKey)}
                    className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 dark:text-white/40 hover:text-gray-600 dark:hover:text-white/60"
                  >
                    {isVisible ? <EyeOff className="h-3 w-3" /> : <Eye className="h-3 w-3" />}
                  </button>
                )}
              </div>
            </div>
          )
        })}

        <Button
          onClick={handleValidate}
          className="w-full h-7 bg-black dark:bg-white text-white dark:text-black text-xs font-medium hover:bg-gray-800 dark:hover:bg-white/90 mt-3"
          disabled={existingKey?.status === "pending"}
        >
          {existingKey?.status === "pending" ? "Testing..." : "Validate"}
        </Button>
      </div>
    </div>
  )
}
