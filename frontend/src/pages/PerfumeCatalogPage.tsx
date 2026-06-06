import { useEffect, useState } from 'react'
import { CatalogFilters } from '../components/CatalogFilters'
import { CompareBar, CompareModal } from '../components/PerfumeComparison'
import { PerfumeCard } from '../components/PerfumeCard'
import { EmptyBlock, ErrorBlock, LoadingBlock } from '../components/StateBlock'
import { useComparePerfumes } from '../hooks/useComparePerfumes'
import { api } from '../lib/api'
import type {
  AromaCategory,
  AromaTag,
  Brand,
  CatalogFilters as CatalogFilterValues,
  Occasion,
  PaginatedApiCollection,
  Perfume,
} from '../types/api'

type PerfumeCatalogPageProps = {
  locationSearch: string
  onNavigate: (to: string) => void
}

const autoApplyFilterKeys = [
  'search',
  'brand',
  'aroma_category',
  'aroma_tag',
  'occasion',
  'price_min',
  'price_max',
] as const satisfies Array<keyof CatalogFilterValues>

const debouncedFilterKeys = new Set<keyof CatalogFilterValues>([
  'search',
  'price_min',
  'price_max',
])
const priceFilterKeys = new Set<keyof CatalogFilterValues>(['price_min', 'price_max'])

const filtersFromSearch = (locationSearch: string): CatalogFilterValues => {
  const params = new URLSearchParams(locationSearch)

  return {
    search: params.get('search') ?? '',
    brand: params.get('brand') ?? '',
    aroma_category: params.get('aroma_category') ?? '',
    aroma_tag: params.get('aroma_tag') ?? '',
    occasion: params.get('occasion') ?? '',
    price_min: params.get('price_min') ?? '',
    price_max: params.get('price_max') ?? '',
    page: params.get('page') ?? '1',
    per_page: params.get('per_page') ?? '12',
  }
}

const queryFromFilters = (filters: CatalogFilterValues, page = '1') => {
  const params = new URLSearchParams()

  Object.entries({ ...filters, page }).forEach(([key, value]) => {
    if (value !== undefined && value !== '') {
      params.set(key, value)
    }
  })

  return params.toString()
}

const changedAutoApplyKeys = (
  filters: CatalogFilterValues,
  activeFilters: CatalogFilterValues,
) =>
  autoApplyFilterKeys.filter(
    (key) => (filters[key] ?? '') !== (activeFilters[key] ?? ''),
  )

type PaginationItem = number | 'ellipsis'

const buildPaginationItems = (
  currentPage: number,
  lastPage: number,
): PaginationItem[] => {
  if (lastPage <= 7) {
    return Array.from({ length: lastPage }, (_, index) => index + 1)
  }

  const pages = new Set([1, lastPage, currentPage])

  if (currentPage > 1) {
    pages.add(currentPage - 1)
  }

  if (currentPage < lastPage) {
    pages.add(currentPage + 1)
  }

  if (currentPage <= 3) {
    pages.add(2)
    pages.add(3)
    pages.add(4)
  }

  if (currentPage >= lastPage - 2) {
    pages.add(lastPage - 3)
    pages.add(lastPage - 2)
    pages.add(lastPage - 1)
  }

  const sortedPages = [...pages]
    .filter((page) => page >= 1 && page <= lastPage)
    .sort((first, second) => first - second)

  return sortedPages.flatMap((page, index) => {
    const previousPage = sortedPages[index - 1]

    if (previousPage && page - previousPage > 1) {
      return ['ellipsis' as const, page]
    }

    return [page]
  })
}

export function PerfumeCatalogPage({
  locationSearch,
  onNavigate,
}: PerfumeCatalogPageProps) {
  const initialFilters = filtersFromSearch(locationSearch)
  const cachedCatalog = api.getCachedPerfumes(initialFilters)
  const cachedBrands = api.getCachedBrands()
  const cachedAromaCategories = api.getCachedAromaCategories()
  const cachedAromaTags = api.getCachedAromaTags()
  const cachedOccasions = api.getCachedOccasions()
  const [filters, setFilters] = useState<CatalogFilterValues>(initialFilters)
  const [catalog, setCatalog] = useState<PaginatedApiCollection<Perfume> | null>(
    cachedCatalog,
  )
  const [brands, setBrands] = useState<Brand[]>(cachedBrands?.data ?? [])
  const [aromaCategories, setAromaCategories] = useState<AromaCategory[]>(
    cachedAromaCategories?.data ?? [],
  )
  const [aromaTags, setAromaTags] = useState<AromaTag[]>(cachedAromaTags?.data ?? [])
  const [occasions, setOccasions] = useState<Occasion[]>(cachedOccasions?.data ?? [])
  const [isLoading, setIsLoading] = useState(!cachedCatalog)
  const [error, setError] = useState<string | null>(null)
  const [isCompareOpen, setIsCompareOpen] = useState(false)
  const compare = useComparePerfumes()

  const removeComparedPerfume = (slug: string) => {
    compare.removePerfume(slug)

    if (isCompareOpen && compare.items.length <= 2) {
      setIsCompareOpen(false)
    }
  }

  const clearComparedPerfumes = () => {
    compare.clearPerfumes()
    setIsCompareOpen(false)
  }

  useEffect(() => {
    let isMounted = true

    Promise.all([
      api.getBrands(),
      api.getAromaCategories(),
      api.getAromaTags(),
      api.getOccasions(),
    ])
      .then(([brandResponse, categoryResponse, tagResponse, occasionResponse]) => {
        if (!isMounted) {
          return
        }

        setBrands(brandResponse.data)
        setAromaCategories(categoryResponse.data)
        setAromaTags(tagResponse.data)
        setOccasions(occasionResponse.data)
      })
      .catch(() => {
        if (isMounted) {
          setError('Pilihan filter belum bisa dimuat. Coba muat ulang halaman.')
        }
      })

    return () => {
      isMounted = false
    }
  }, [])

  useEffect(() => {
    let isMounted = true
    const activeFilters = filtersFromSearch(locationSearch)
    const cachedResponse = api.getCachedPerfumes(activeFilters)

    Promise.resolve().then(() => {
      if (!isMounted) {
        return
      }

      setFilters(activeFilters)
      setIsLoading(!cachedResponse)
      setError(null)

      if (cachedResponse) {
        setCatalog(cachedResponse)
      }
    })

    api
      .getPerfumes(activeFilters)
      .then((response) => {
        if (isMounted) {
          setCatalog(response)
          setError(null)
        }
      })
      .catch(() => {
        if (isMounted && !cachedResponse) {
          setError('Katalog parfum belum bisa dimuat. Coba muat ulang halaman atau kembali beberapa saat lagi.')
        }
      })
      .finally(() => {
        if (isMounted) {
          setIsLoading(false)
        }
      })

    return () => {
      isMounted = false
    }
  }, [locationSearch])

  useEffect(() => {
    const activeFilters = filtersFromSearch(locationSearch)
    const changedKeys = changedAutoApplyKeys(filters, activeFilters)

    if (changedKeys.length === 0) {
      return
    }

    const query = queryFromFilters(
      {
        ...activeFilters,
        ...Object.fromEntries(
          autoApplyFilterKeys.map((key) => [key, filters[key] ?? '']),
        ),
      },
      '1',
    )
    const target = `/parfum${query ? `?${query}` : ''}`
    const shouldDebounce = changedKeys.some((key) => debouncedFilterKeys.has(key))
    const debounceDelay = changedKeys.some((key) => priceFilterKeys.has(key))
      ? 1300
      : 450
    const applyFilters = () => onNavigate(target)

    if (!shouldDebounce) {
      applyFilters()
      return
    }

    const timeoutId = window.setTimeout(applyFilters, debounceDelay)

    return () => window.clearTimeout(timeoutId)
  }, [filters, locationSearch, onNavigate])

  const submitFilters = () => {
    const query = queryFromFilters(filters, '1')
    onNavigate(`/parfum${query ? `?${query}` : ''}`)
  }

  const resetFilters = () => {
    onNavigate('/parfum')
  }

  const goToPage = (page: number) => {
    const activeFilters = filtersFromSearch(locationSearch)
    const query = queryFromFilters(activeFilters, String(page))

    onNavigate(`/parfum${query ? `?${query}` : ''}`)
  }

  const catalogReturnTo = `/parfum${locationSearch}`
  const paginationItems = catalog
    ? buildPaginationItems(catalog.meta.current_page, catalog.meta.last_page)
    : []

  return (
    <main
      className={`page page--catalog ${compare.items.length > 0 ? 'page--compare-active' : ''}`}
    >
      <section className="catalog-hero">
        <div>
          <p className="eyebrow">Katalog parfum lokal</p>
          <h1>Jelajahi parfum lokal</h1>
          <p>
            Gunakan filter aroma, kebutuhan, brand, dan harga untuk mempersempit
            pilihan parfum yang bisa kamu jelajahi.
          </p>
        </div>
        <div className="catalog-hero__note">
          <strong>{catalog?.meta.total ?? '...'}</strong>
          <span>parfum tersedia</span>
        </div>
      </section>

      <div className="catalog-layout">
        <CatalogFilters
          filters={filters}
          brands={brands}
          aromaCategories={aromaCategories}
          aromaTags={aromaTags}
          occasions={occasions}
          onChange={setFilters}
          onSubmit={submitFilters}
          onReset={resetFilters}
        />

        <section className="catalog-results">
          {isLoading ? (
            <LoadingBlock
              title="Sedang memuat katalog parfum"
              message="Kami sedang menyiapkan parfum dan filter yang kamu pilih."
            />
          ) : error ? (
            <ErrorBlock
              title="Katalog belum tersedia"
              message={error}
            />
          ) : catalog && catalog.data.length > 0 ? (
            <>
              <div className="result-summary">
                <p>
                  Menampilkan {catalog.meta.from} - {catalog.meta.to} dari{' '}
                  {catalog.meta.total} parfum.
                </p>
              </div>
              <div className="perfume-grid">
                {catalog.data.map((perfume) => (
                  <PerfumeCard
                    key={perfume.slug}
                    perfume={perfume}
                    detailReturnTo={catalogReturnTo}
                    isCompareSelected={compare.isSelected(perfume.slug)}
                    isCompareDisabled={
                      compare.isAtLimit && !compare.isSelected(perfume.slug)
                    }
                    onToggleCompare={() => compare.togglePerfume(perfume)}
                    onNavigate={onNavigate}
                  />
                ))}
              </div>
              <div className="pagination">
                <button
                  className="button button--secondary"
                  type="button"
                  disabled={catalog.meta.current_page <= 1}
                  onClick={() => goToPage(catalog.meta.current_page - 1)}
                >
                  Sebelumnya
                </button>
                <div className="pagination__pages" aria-label="Nomor halaman">
                  {paginationItems.map((item, index) =>
                    item === 'ellipsis' ? (
                      <span
                        className="pagination__ellipsis"
                        key={`ellipsis-${index}`}
                        aria-hidden="true"
                      >
                        ...
                      </span>
                    ) : (
                      <button
                        className={`pagination__page ${item === catalog.meta.current_page ? 'pagination__page--current' : ''}`}
                        type="button"
                        key={item}
                        aria-current={
                          item === catalog.meta.current_page ? 'page' : undefined
                        }
                        onClick={() => goToPage(item)}
                      >
                        {item}
                      </button>
                    ),
                  )}
                </div>
                <span className="pagination__status">
                  Halaman {catalog.meta.current_page} dari {catalog.meta.last_page}
                </span>
                <button
                  className="button button--secondary"
                  type="button"
                  disabled={catalog.meta.current_page >= catalog.meta.last_page}
                  onClick={() => goToPage(catalog.meta.current_page + 1)}
                >
                  Berikutnya
                </button>
              </div>
            </>
          ) : (
            <EmptyBlock
              title="Belum ketemu parfum yang sesuai"
              message="Coba longgarkan filter atau gunakan kata kunci yang lebih umum."
              actionLabel="Atur ulang filter"
              onAction={resetFilters}
            />
          )}
        </section>
      </div>

      {compare.items.length > 0 ? (
        <CompareBar
          items={compare.items}
          maxItems={compare.maxItems}
          onCompare={() => setIsCompareOpen(true)}
          onRemove={removeComparedPerfume}
          onClear={clearComparedPerfumes}
        />
      ) : null}
      {isCompareOpen ? (
        <CompareModal
          items={compare.items}
          returnTo={catalogReturnTo}
          onClose={() => setIsCompareOpen(false)}
          onRemove={removeComparedPerfume}
          onNavigate={onNavigate}
        />
      ) : null}
    </main>
  )
}
