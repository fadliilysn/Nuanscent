import type {
  ApiCollection,
  ApiResource,
  AromaCategory,
  AromaTag,
  Brand,
  CatalogFilters,
  Occasion,
  PaginatedApiCollection,
  Perfume,
} from '../types/api'

const API_BASE_URL = (
  import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api'
).replace(/\/$/, '')

const buildQuery = (filters: CatalogFilters) => {
  const params = new URLSearchParams()

  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== null && String(value).trim() !== '') {
      params.set(key, String(value).trim())
    }
  })

  return params.toString()
}

const fetchJson = async <T>(path: string): Promise<T> => {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: 'application/json',
    },
  })

  if (!response.ok) {
    throw new Error(`Permintaan API gagal dengan status ${response.status}.`)
  }

  return response.json() as Promise<T>
}

export const api = {
  getPerfumes(filters: CatalogFilters) {
    const query = buildQuery(filters)

    return fetchJson<PaginatedApiCollection<Perfume>>(
      `/perfumes${query ? `?${query}` : ''}`,
    )
  },
  getPerfume(slug: string) {
    return fetchJson<ApiResource<Perfume>>(`/perfumes/${encodeURIComponent(slug)}`)
  },
  getBrands() {
    return fetchJson<ApiCollection<Brand>>('/brands')
  },
  getAromaCategories() {
    return fetchJson<ApiCollection<AromaCategory>>('/aroma-categories')
  },
  getAromaTags() {
    return fetchJson<ApiCollection<AromaTag>>('/aroma-tags')
  },
  getOccasions() {
    return fetchJson<ApiCollection<Occasion>>('/occasions')
  },
}
