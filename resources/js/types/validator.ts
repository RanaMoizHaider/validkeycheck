export enum ValidationStatus {
  VALID = 'valid',
  INVALID = 'invalid',
  FORBIDDEN = 'forbidden',
  RATE_LIMITED = 'rate_limited',
  UNAVAILABLE = 'unavailable'
}

export interface Provider {
  slug: string;
  name: string;
  category: string;
  description: string;
  required_fields: string[] | { [key: string]: string };
  documentation_url?: string;
  base_url?: string;
  website_url?: string;
  api_keys_url?: string;
}

export interface Category {
  slug: string;
  name: string;
  description: string;
  providers: Provider[];
}

export interface ValidationResult {
  success: boolean;
  status: ValidationStatus;
  message: string;
  provider: string;
  code?: string;
  metadata?: { [key: string]: any };
  status_class: string;
  status_label: string;
} 