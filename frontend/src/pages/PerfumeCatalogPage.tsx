import { useEffect, useState } from 'react'
import { CatalogFilters } from '../components/CatalogFilters'
import { PerfumeCard } from '../components/PerfumeCard'
import { EmptyBlock, ErrorBlock, LoadingBlock } from '../components/StateBlock'
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

export function PerfumeCatalogPage({
  locationSearch,
  onNavigate,
}: PerfumeCatalogPageProps) {
  const [filters, setFilters] = useState<CatalogFilterValues>(
    filtersFromSearch(locationSearch),
  )
  const [catalog, setCatalog] = useState<PaginatedApiCollection<Perfume> | null>(
    null,
  )
  const [brands, setBrands] = useState<Brand[]>([])
  const [aromaCategories, setAromaCategories] = useState<AromaCategory[]>([])
  const [aromaTags, setAromaTags] = useState<AromaTag[]>([])
  const [occasions, setOccasions] = useState<Occasion[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

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
          setError('Data filter belum bisa dimuat. Pastikan API Laravel aktif.')
        }
      })

    return () => {
      isMounted = false
    }
  }, [])

  useEffect(() => {
    let isMounted = true
    const activeFilters = filtersFromSearch(locationSearch)

    api
      .getPerfumes(activeFilters)
      .then((response) => {
        if (isMounted) {
          setCatalog(response)
        }
      })
      .catch(() => {
        if (isMounted) {
          setError('Katalog parfum belum bisa dimuat. Cek koneksi ke API.')
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

  return (
    <main className="page">
      <section className="catalog-hero">
        <div>
          <p className="eyebrow">Katalog parfum lokal</p>
          <h1>Temukan parfum yang lebih mudah dipahami.</h1>
          <p>
            Jelajahi parfum lokal Indonesia dengan filter aroma, occasion, brand,
            dan rentang harga. Data hanya menampilkan parfum yang sudah dipublish.
          </p>
        </div>
        <div className="catalog-hero__note">
          <strong>{catalog?.meta.total ?? 0}</strong>
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
            <LoadingBlock />
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
                <span>
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
              title="Belum ada parfum yang cocok"
              message="Coba ubah filter, atau pastikan ada data parfum dengan status published di backend."
              actionLabel="Reset filter"
              onAction={resetFilters}
            />
          )}
        </section>
      </div>
    </main>
  )
}
