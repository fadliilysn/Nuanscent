import { useEffect, useState, type MouseEvent } from 'react'
import { PerfumeCard } from '../components/PerfumeCard'
import { EmptyBlock, ErrorBlock, LoadingBlock } from '../components/StateBlock'
import { api } from '../lib/api'
import type { Brand, Perfume } from '../types/api'

type BrandDetailPageProps = {
  slug: string
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

const formatPerfumeCount = (brand: Brand, perfumes: Perfume[]) => {
  const count = brand.perfumes_count ?? brand.perfumes?.length ?? perfumes.length

  return `${count} parfum`
}

export function BrandDetailPage({ slug, onNavigate }: BrandDetailPageProps) {
  const [brand, setBrand] = useState<Brand | null>(null)
  const [perfumes, setPerfumes] = useState<Perfume[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true

    api
      .getBrand(slug)
      .then((brandResponse) => {
        if (!isMounted) {
          return
        }

        setBrand(brandResponse.data)
        setPerfumes(brandResponse.data.perfumes ?? [])
      })
      .catch(() => {
        if (isMounted) {
          setError('Detail merek belum bisa dimuat atau belum tersedia.')
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
  }, [slug])

  if (isLoading) {
    return (
      <main className="page page--compact">
        <LoadingBlock />
      </main>
    )
  }

  if (error || !brand) {
    return (
      <main className="page page--compact">
        <ErrorBlock
          title="Brand belum tersedia"
          message={error ?? 'Brand ini belum bisa ditampilkan.'}
          actionLabel="Kembali ke semua brands"
          onAction={() => onNavigate('/brands')}
        />
      </main>
    )
  }

  const catalogPath = `/parfum?brand=${encodeURIComponent(brand.slug)}`
  const brandReturnTo = `/brands/${brand.slug}`

  return (
    <main className="page brand-page">
      <button className="back-link" type="button" onClick={() => onNavigate('/brands')}>
        Kembali ke semua brands
      </button>

      <section className="brand-detail-hero">
        <div className="brand-detail-profile">
          <BrandLogo brand={brand} />
          <div>
            <p className="eyebrow">Profil brand</p>
            <h1>{brand.name}</h1>
            <p>{brand.description ?? 'Deskripsi brand belum tersedia dari sumber data.'}</p>
          </div>
        </div>
        <aside className="brand-detail-summary">
          <strong>{formatPerfumeCount(brand, perfumes)}</strong>
          <span>tersedia di katalog</span>
          {brand.official_website ? (
            <a href={brand.official_website} target="_blank" rel="noreferrer">
              Website resmi
            </a>
          ) : null}
        </aside>
      </section>

      <div className="brand-detail-actions">
        <a
          className="button button--primary"
          href={catalogPath}
          onClick={(event) => preventAndNavigate(event, catalogPath, onNavigate)}
        >
          Lihat parfum brand ini di katalog
        </a>
        <a
          className="button button--ghost"
          href="/brands"
          onClick={(event) => preventAndNavigate(event, '/brands', onNavigate)}
        >
          Kembali ke semua brands
        </a>
      </div>

      <section className="brand-perfume-section">
        <div className="brand-section-header">
          <div>
            <p className="eyebrow">Parfum dari {brand.name}</p>
            <h2>Koleksi yang sudah bisa dijelajahi.</h2>
          </div>
        </div>

        {perfumes.length > 0 ? (
          <div className="perfume-grid">
            {perfumes.map((perfume) => (
              <PerfumeCard
                key={perfume.slug}
                perfume={perfume}
                detailReturnTo={brandReturnTo}
                onNavigate={onNavigate}
              />
            ))}
          </div>
        ) : (
          <EmptyBlock
            title="Belum ada parfum published"
            message="Brand ini belum memiliki parfum published di katalog publik."
            actionLabel="Buka katalog"
            onAction={() => onNavigate('/parfum')}
          />
        )}
      </section>
    </main>
  )
}

function BrandLogo({ brand }: { brand: Brand }) {
  return (
    <div className="brand-logo brand-logo--detail" aria-hidden="true">
      {brand.logo_url ? (
        <img src={brand.logo_url} alt="" loading="lazy" />
      ) : (
        <span>{brand.name.slice(0, 1).toUpperCase()}</span>
      )}
    </div>
  )
}
