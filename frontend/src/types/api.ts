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

export type IntensityPreference = 'soft' | 'medium' | 'strong' | 'no_preference'

export type BlindBuyComfort = 'safe' | 'flexible' | 'adventurous'

export type MarketedGenderPreference =
  | 'no_preference'
  | 'unisex'
  | 'pria'
  | 'wanita'
  | 'maskulin'
  | 'feminin'
  | 'male'
  | 'female'

export type RecommendationRequestPayload = {
  occasion: string
  aroma_preference: string
  price_min: number | null
  price_max: number | null
  intensity_preference: IntensityPreference
  avoided_tags: string[]
  blind_buy_comfort: BlindBuyComfort
  marketed_gender_preference: MarketedGenderPreference
}

export type BlindBuyCautionLabel =
  | 'Cenderung Aman'
  | 'Perlu Pertimbangan'
  | 'Sebaiknya Coba Sample Dulu'
  | 'Data Belum Cukup'

export type BlindBuyCaution = {
  label: BlindBuyCautionLabel
  reasons: string[]
}

export type Recommendation = {
  id: number
  slug: string
  name: string
  image_url: string | null
  price_min: number | null
  price_max: number | null
  brand: Pick<Brand, 'id' | 'name' | 'slug'> | null
  main_aroma_category: Pick<AromaCategory, 'id' | 'name' | 'slug'> | null
  match_score: number
  match_percentage: number
  matched_reasons: string[]
  blind_buy_caution: BlindBuyCaution
  aroma_tags: Array<Pick<AromaTag, 'id' | 'name' | 'slug' | 'is_polarizing'>>
  occasions: Array<Pick<Occasion, 'id' | 'name' | 'slug'>>
  data_limitations: string[]
}

export type RecommendationResponse = {
  recommendations: Recommendation[]
}
