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