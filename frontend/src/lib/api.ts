import type {
  ApiCollection,
  ApiResource,
  AromaCategory,
  AromaTag,
  Brand,
  CatalogFilters,
  Guide,
  Occasion,
  PaginatedApiCollection,
  Perfume,
  RecommendationRequestPayload,
  RecommendationResponse,
} from '../types/api'

const API_BASE_URL = (
  import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api'
).replace(/\/$/, '')

const minute = 60 * 1000
const ttl = {
  reference: 10 * minute,
  guides: 10 * minute,
  detail: 5 * minute,
  catalog: 2 * minute,
}

type CacheEntry<T> = {
  data?: T
  expiresAt: number
  promise?: Promise<T>
}

const getCache = new Map<string, CacheEntry<unknown>>()

const buildQuery = (filters: CatalogFilters) => {
  const params = new URLSearchParams()

  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== null && String(value).trim() !== '') {
      params.set(key, String(value).trim())
    }
  })

  return params.toString()
}

const cacheKeyFor = (path: string) => `${API_BASE_URL}${path}`

const readCachedJson = <T>(path: string): T | null => {
  const entry = getCache.get(cacheKeyFor(path)) as CacheEntry<T> | undefined

  if (!entry || entry.data === undefined || entry.expiresAt <= Date.now()) {
    return null
  }

  return entry.data
}

const fetchJson = async <T>(
  path: string,
  options: { ttlMs?: number; bypassCache?: boolean } = {},
): Promise<T> => {
  const cacheKey = cacheKeyFor(path)
  const ttlMs = options.ttlMs ?? 0
  const cachedEntry = getCache.get(cacheKey) as CacheEntry<T> | undefined

  if (
    ttlMs > 0 &&
    !options.bypassCache &&
    cachedEntry?.data !== undefined &&
    cachedEntry.expiresAt > Date.now()
  ) {
    return cachedEntry.data
  }

  if (
    ttlMs > 0 &&
    !options.bypassCache &&
    cachedEntry?.promise &&
    cachedEntry.expiresAt > Date.now()
  ) {
    return cachedEntry.promise
  }

  const promise = fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: 'application/json',
    },
  }).then((response) => {
    if (!response.ok) {
      throw new Error('Permintaan belum berhasil. Coba lagi sebentar.')
    }

    return response.json() as Promise<T>
  })

  if (ttlMs > 0) {
    getCache.set(cacheKey, {
      expiresAt: Date.now() + ttlMs,
      promise,
    })

    try {
      const data = await promise

      getCache.set(cacheKey, {
        data,
        expiresAt: Date.now() + ttlMs,
      })

      return data
    } catch (error) {
      getCache.delete(cacheKey)
      throw error
    }
  }

  return promise
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
    let message = 'Permintaan belum berhasil. Coba lagi sebentar.'

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
      // Keep the generic message when the response does not return JSON.
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
      { ttlMs: ttl.catalog },
    )
  },
  getCachedPerfumes(filters: CatalogFilters) {
    const query = buildQuery(filters)

    return readCachedJson<PaginatedApiCollection<Perfume>>(
      `/perfumes${query ? `?${query}` : ''}`,
    )
  },
  getPerfume(slug: string) {
    return fetchJson<ApiResource<Perfume>>(`/perfumes/${encodeURIComponent(slug)}`, {
      ttlMs: ttl.detail,
    })
  },
  getCachedPerfume(slug: string) {
    return readCachedJson<ApiResource<Perfume>>(`/perfumes/${encodeURIComponent(slug)}`)
  },
  getBrands() {
    return fetchJson<ApiCollection<Brand>>('/brands', { ttlMs: ttl.reference })
  },
  getCachedBrands() {
    return readCachedJson<ApiCollection<Brand>>('/brands')
  },
  getBrand(slug: string) {
    return fetchJson<ApiResource<Brand>>(`/brands/${encodeURIComponent(slug)}`, {
      ttlMs: ttl.detail,
    })
  },
  getCachedBrand(slug: string) {
    return readCachedJson<ApiResource<Brand>>(`/brands/${encodeURIComponent(slug)}`)
  },
  getGuides() {
    return fetchJson<ApiCollection<Guide>>('/guides', { ttlMs: ttl.guides })
  },
  getCachedGuides() {
    return readCachedJson<ApiCollection<Guide>>('/guides')
  },
  getGuide(slug: string) {
    return fetchJson<ApiResource<Guide>>(`/guides/${encodeURIComponent(slug)}`, {
      ttlMs: ttl.guides,
    })
  },
  getCachedGuide(slug: string) {
    return readCachedJson<ApiResource<Guide>>(`/guides/${encodeURIComponent(slug)}`)
  },
  getAromaCategories() {
    return fetchJson<ApiCollection<AromaCategory>>('/aroma-categories', {
      ttlMs: ttl.reference,
    })
  },
  getCachedAromaCategories() {
    return readCachedJson<ApiCollection<AromaCategory>>('/aroma-categories')
  },
  getAromaTags() {
    return fetchJson<ApiCollection<AromaTag>>('/aroma-tags', { ttlMs: ttl.reference })
  },
  getCachedAromaTags() {
    return readCachedJson<ApiCollection<AromaTag>>('/aroma-tags')
  },
  getOccasions() {
    return fetchJson<ApiCollection<Occasion>>('/occasions', { ttlMs: ttl.reference })
  },
  getCachedOccasions() {
    return readCachedJson<ApiCollection<Occasion>>('/occasions')
  },
  getRecommendations(payload: RecommendationRequestPayload) {
    return postJson<RecommendationResponse, RecommendationRequestPayload>(
      '/recommendations',
      payload,
    )
  },
}
