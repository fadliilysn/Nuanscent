import { useEffect, useState, type MouseEvent } from 'react'
import { PerfumeCard } from '../components/PerfumeCard'
import { api } from '../lib/api'
import type { AromaCategory, Brand, Perfume } from '../types/api'

type HomePageProps = {
  onNavigate: (to: string) => void
}

type HomeData = {
  brands: Brand[]
  aromaCategories: AromaCategory[]
  perfumes: Perfume[]
}

const initialHomeData: HomeData = {
  brands: [],
  aromaCategories: [],
  perfumes: [],
}

const readCachedHomeData = (): HomeData => ({
  brands: api.getCachedBrands()?.data ?? [],
  aromaCategories: api.getCachedAromaCategories()?.data ?? [],
  perfumes: api.getCachedPerfumes({ per_page: '6' })?.data ?? [],
})

const hasCompleteHomeCache = (data: HomeData) =>
  data.brands.length > 0 && data.aromaCategories.length > 0 && data.perfumes.length > 0

const preventAndNavigate = (
  event: MouseEvent<HTMLAnchorElement>,
  to: string,
  onNavigate: (to: string) => void,
) => {
  event.preventDefault()
  onNavigate(to)
}

export function HomePage({ onNavigate }: HomePageProps) {
  const cachedHomeData = readCachedHomeData()
  const [homeData, setHomeData] = useState<HomeData>(
    hasCompleteHomeCache(cachedHomeData) ? cachedHomeData : initialHomeData,
  )
  const [isLoading, setIsLoading] = useState(!hasCompleteHomeCache(cachedHomeData))
  const [hasLoadError, setHasLoadError] = useState(false)

  useEffect(() => {
    let isMounted = true
    const cachedData = readCachedHomeData()

    Promise.allSettled([
      api.getBrands(),
      api.getAromaCategories(),
      api.getPerfumes({ per_page: '6' }),
    ])
      .then(([brandResult, categoryResult, perfumeResult]) => {
        if (!isMounted) {
          return
        }

        setHomeData({
          brands:
            brandResult.status === 'fulfilled'
              ? brandResult.value.data
              : cachedData.brands,
          aromaCategories:
            categoryResult.status === 'fulfilled'
              ? categoryResult.value.data
              : cachedData.aromaCategories,
          perfumes:
            perfumeResult.status === 'fulfilled'
              ? perfumeResult.value.data
              : cachedData.perfumes,
        })

        setHasLoadError(
          brandResult.status === 'rejected' ||
            categoryResult.status === 'rejected' ||
            perfumeResult.status === 'rejected',
        )
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
    <main className="page home-page">
      <section className="home-hero">
        <div className="home-hero__content">
          <div className="home-hero__brand">
            <img
              src="/images/logo-nuanscent.png"
              alt=""
              aria-hidden="true"
              decoding="async"
            />
            <div>
              <strong>Nuanscent</strong>
              <span>Katalog parfum lokal</span>
            </div>
          </div>
          <h1>Temukan parfum lokal tanpa harus menebak-nebak.</h1>
          <p>
            Nuanscent membantu kamu memilih parfum lokal Indonesia lewat quiz
            sederhana, katalog terfilter, dan penjelasan yang ramah untuk pemula.
          </p>
          <div className="home-hero__actions">
            <a
              className="button button--primary"
              href="/quiz"
              onClick={(event) => preventAndNavigate(event, '/quiz', onNavigate)}
            >
              Mulai quiz
            </a>
            <a
              className="button button--secondary"
              href="/parfum"
              onClick={(event) => preventAndNavigate(event, '/parfum', onNavigate)}
            >
              Jelajahi katalog
            </a>
          </div>
        </div>
      </section>

      <section className="home-section">
        <SectionHeader
          eyebrow="Mulai dari mana?"
          title="Pilih jalur yang paling sesuai dengan cara kamu mencari parfum."
        />
        <div className="home-choice-grid">
          <a
            className="home-choice-card"
            href="/quiz"
            onClick={(event) => preventAndNavigate(event, '/quiz', onNavigate)}
          >
            <span>01</span>
            <div>
              <h3>Masih bingung aromanya?</h3>
              <p>Jawab quiz singkat agar Nuanscent memberi kandidat yang lebih relevan.</p>
            </div>
          </a>
          <a
            className="home-choice-card"
            href="/parfum"
            onClick={(event) => preventAndNavigate(event, '/parfum', onNavigate)}
          >
            <span>02</span>
            <div>
              <h3>Mau lihat semua pilihan?</h3>
              <p>Masuk ke katalog dan filter parfum berdasarkan brand, aroma, dan harga.</p>
            </div>
          </a>
          <a
            className="home-choice-card"
            href="/guides"
            onClick={(event) => preventAndNavigate(event, '/guides', onNavigate)}
          >
            <span>03</span>
            <div>
              <h3>Mau belajar istilah parfum?</h3>
              <p>Baca panduan aroma, notes, dan tips blind-buy untuk pemula.</p>
            </div>
          </a>
        </div>
      </section>

      <section className="home-section">
        <SectionHeader
          eyebrow="Brand lokal"
          title="Mulai dari brand yang sudah ada di katalog."
          actionLabel="Lihat semua brands"
          actionHref="/brands"
          onNavigate={onNavigate}
        />
        {isLoading ? (
          <InlineState message="Sedang memuat brand lokal." />
        ) : homeData.brands.length > 0 ? (
          <div className="home-brand-grid">
            {homeData.brands.map((brand) => {
              const brandPath = `/brands/${encodeURIComponent(brand.slug)}`

              return (
                <a
                  className="home-brand-card"
                  href={brandPath}
                  key={brand.slug}
                  onClick={(event) => preventAndNavigate(event, brandPath, onNavigate)}
                >
                  <BrandLogo brand={brand} />
                  <div>
                    <h3>{brand.name}</h3>
                  </div>
                </a>
              )
            })}
          </div>
        ) : (
          <InlineState message="Brand belum bisa ditampilkan saat ini. Coba lagi sebentar." />
        )}
      </section>

      <section className="home-section">
        <SectionHeader
          eyebrow="Eksplorasi aroma"
          title="Cari dari keluarga aroma yang paling dekat dengan seleramu."
        />
        {isLoading ? (
          <InlineState message="Sedang memuat kategori aroma." />
        ) : homeData.aromaCategories.length > 0 ? (
          <div className="home-aroma-grid">
            {homeData.aromaCategories.map((category, index) => {
              const categoryPath = `/parfum?aroma_category=${encodeURIComponent(category.slug)}`

              return (
                <a
                  className={`home-aroma-card home-aroma-card--${(index % 5) + 1}`}
                  href={categoryPath}
                  key={category.slug}
                  onClick={(event) => preventAndNavigate(event, categoryPath, onNavigate)}
                >
                  <h3>{category.name}</h3>
                </a>
              )
            })}
          </div>
        ) : (
          <InlineState message="Kategori aroma belum bisa ditampilkan saat ini. Coba lagi sebentar." />
        )}
      </section>

      <section className="home-section">
        <SectionHeader
          eyebrow="Preview katalog"
          title="Beberapa parfum lokal untuk mulai dijelajahi."
          actionLabel="Buka katalog lengkap"
          actionHref="/parfum"
          onNavigate={onNavigate}
        />
        {hasLoadError ? (
          <InlineState message="Sebagian isi halaman belum bisa dimuat. Kamu tetap bisa membuka katalog lewat tombol di atas." />
        ) : null}
        {isLoading ? (
          <InlineState message="Sedang memuat preview parfum." />
        ) : homeData.perfumes.length > 0 ? (
          <div className="perfume-grid home-perfume-grid">
            {homeData.perfumes.map((perfume) => (
              <PerfumeCard
                key={perfume.slug}
                perfume={perfume}
                onNavigate={onNavigate}
              />
            ))}
          </div>
        ) : (
          <InlineState message="Preview parfum belum tersedia. Silakan buka katalog untuk mencoba lagi." />
        )}
      </section>
    </main>
  )
}

function SectionHeader({
  eyebrow,
  title,
  actionLabel,
  actionHref,
  onNavigate,
}: {
  eyebrow: string
  title: string
  actionLabel?: string
  actionHref?: string
  onNavigate?: (to: string) => void
}) {
  return (
    <div className="home-section__header">
      <div>
        <p className="eyebrow">{eyebrow}</p>
        <h2>{title}</h2>
      </div>
      {actionLabel && actionHref && onNavigate ? (
        <a
          className="button button--ghost"
          href={actionHref}
          onClick={(event) => preventAndNavigate(event, actionHref, onNavigate)}
        >
          {actionLabel}
        </a>
      ) : null}
    </div>
  )
}

function InlineState({ message }: { message: string }) {
  return <p className="home-inline-state">{message}</p>
}

function BrandLogo({ brand }: { brand: Brand }) {
  return (
    <span className="brand-logo" aria-hidden="true">
      {brand.logo_url ? (
        <img src={brand.logo_url} alt="" loading="lazy" decoding="async" />
      ) : (
        <span>{brand.name.slice(0, 1).toUpperCase()}</span>
      )}
    </span>
  )
}
