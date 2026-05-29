import { useEffect, useState, type MouseEvent } from 'react'
import { EmptyBlock, ErrorBlock, LoadingBlock } from '../components/StateBlock'
import { api } from '../lib/api'
import type { Brand } from '../types/api'

type BrandListPageProps = {
  onNavigate: (to: string) => void
}

const preventAndNavigate = (
  event: MouseEvent<HTMLAnchorElement>,
  to: string,
  onNavigate: (to: string) => void,
) => {
  event.preventDefault()
  onNavigate(to)
}

const formatPerfumeCount = (brand: Brand) => {
  const count = brand.perfumes_count ?? brand.perfumes?.length

  return typeof count === 'number' ? `${count} parfum` : 'Koleksi tersedia'
}

export function BrandListPage({ onNavigate }: BrandListPageProps) {
  const cachedBrands = api.getCachedBrands()
  const [brands, setBrands] = useState<Brand[]>(cachedBrands?.data ?? [])
  const [isLoading, setIsLoading] = useState(!cachedBrands)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true
    const hadCachedBrands = Boolean(api.getCachedBrands())

    api
      .getBrands()
      .then((response) => {
        if (isMounted) {
          setBrands(response.data)
        }
      })
      .catch(() => {
        if (isMounted && !hadCachedBrands) {
          setError('Daftar brand belum bisa dimuat. Coba muat ulang halaman.')
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
  }, [])

  return (
    <main className="page brand-page">
      <section className="brand-hero">
        <p className="eyebrow">Brands parfum lokal</p>
        <h1>Kenali brand sebelum memilih parfumnya.</h1>
        <p>
          Jelajahi brand lokal yang sudah masuk katalog Nuanscent, lalu masuk ke
          koleksi parfumnya atau lihat langsung di katalog terfilter.
        </p>
      </section>

      {isLoading ? (
        <LoadingBlock />
      ) : error ? (
        <ErrorBlock title="Merek belum tersedia" message={error} />
      ) : brands.length > 0 ? (
        <section className="brand-list" aria-label="Daftar merek parfum lokal">
          {brands.map((brand) => {
            const detailPath = `/brands/${brand.slug}`
            const catalogPath = `/parfum?brand=${encodeURIComponent(brand.slug)}`

            return (
              <article className="brand-list-item" key={brand.slug}>
                <BrandLogo brand={brand} />
                <div className="brand-list-item__main">
                  <div>
                    <h2>{brand.name}</h2>
                    <p>
                      {brand.description ??
                        'Deskripsi brand belum tersedia, tapi koleksi parfumnya tetap bisa kamu lihat di katalog.'}
                    </p>
                  </div>
                  <span>{formatPerfumeCount(brand)}</span>
                </div>
                <div className="brand-list-item__actions">
                  <a
                    className="button button--primary"
                    href={detailPath}
                    onClick={(event) => preventAndNavigate(event, detailPath, onNavigate)}
                  >
                    Lihat brand
                  </a>
                  <a
                    className="button button--ghost"
                    href={catalogPath}
                    onClick={(event) => preventAndNavigate(event, catalogPath, onNavigate)}
                  >
                    Lihat parfum brand ini
                  </a>
                </div>
              </article>
            )
          })}
        </section>
      ) : (
        <EmptyBlock
          title="Belum ada brand"
          message="Brand belum bisa ditampilkan saat ini. Coba kembali beberapa saat lagi."
        />
      )}
    </main>
  )
}

function BrandLogo({ brand }: { brand: Brand }) {
  return (
    <div className="brand-logo brand-logo--list" aria-hidden="true">
      {brand.logo_url ? (
        <img src={brand.logo_url} alt="" loading="lazy" decoding="async" />
      ) : (
        <span>{brand.name.slice(0, 1).toUpperCase()}</span>
      )}
    </div>
  )
}
