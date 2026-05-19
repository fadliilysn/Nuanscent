export type ApiCollection<T> = {
  data: T[]
}

export type PaginatedApiCollection<T> = ApiCollection<T> & {
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    from: number | null
    last_page: number
    path: string
    per_page: number
    to: number | null
    total: number
  }
}

export type ApiResource<T> = {
  data: T
}

export type Brand = {
  id: number
  name: string
  slug: string
  description: string | null
  official_website: string | null
  logo_url: string | null
  perfumes_count?: number
  perfumes?: Perfume[]
}

export type AromaCategory = {
  id: number
  name: string
  slug: string
  description: string | null
}

export type AromaTag = {
  id: number
  name: string
  slug: string
  description: string | null
  is_polarizing: boolean
}

export type Occasion = {
  id: number
  name: string
  slug: string
  description: string | null
}

export type NotePosition = 'top' | 'middle' | 'base' | 'unspecified'

export type Note = {
  id: number
  name: string
  slug: string
  description_simple: string | null
  note_family: string | null
  position?: NotePosition
}

export type PerfumeSource = {
  url: string | null
  name: string | null
  last_verified_at: string | null
}

export type PerfumeVariant = {
  id: number
  label: string | null
  volume_ml: number | null
  price: number | null
}

export type Perfume = {
  id: number
  name: string
  slug: string
  short_description: string | null
  official_description?: string | null
  concentration: string | null
  volume_ml: number | null
  price_min: number | null
  price_max: number | null
  variants?: PerfumeVariant[]
  image_url: string | null
  marketed_gender: string | null
  intensity: string | null
  source: PerfumeSource
  brand?: Brand
  main_aroma_category?: AromaCategory
  aroma_tags?: AromaTag[]
  occasions?: Occasion[]
  notes?: Note[]
}

export type CatalogFilters = {
  search?: string
  brand?: string
  aroma_category?: string
  aroma_tag?: string
  occasion?: string
  price_min?: string
  price_max?: string
  page?: string
  per_page?: string
}
