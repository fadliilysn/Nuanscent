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
  RecommendationRequestPayload,
  RecommendationResponse,
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

const postJson = async <TResponse, TPayload>(
  path: string,
  payload: TPayload,
): Promise<TResponse> => {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  })

  if (!response.ok) {
    let message = `Permintaan API gagal dengan status ${response.status}.`

    try {
      const errorBody = (await response.json()) as {
        message?: string
        errors?: Record<string, string[]>
      }
      const firstValidationMessage = errorBody.errors
        ? Object.values(errorBody.errors).flat()[0]
        : null

      message = firstValidationMessage ?? errorBody.message ?? message
    } catch {
      // Keep the generic message when the API does not return JSON.
    }

    throw new Error(message)
  }

  return response.json() as Promise<TResponse>
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
  getRecommendations(payload: RecommendationRequestPayload) {
    return postJson<RecommendationResponse, RecommendationRequestPayload>(
      '/recommendations',
      payload,
    )
  },
}
