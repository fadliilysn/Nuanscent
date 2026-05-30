import { useEffect, useState, type MouseEvent } from 'react'
import { EmptyBlock, ErrorBlock, LoadingBlock } from '../components/StateBlock'
import { api } from '../lib/api'
import type { Guide } from '../types/api'

type GuideListPageProps = {
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

const formatPublishedDate = (publishedAt: string | null) => {
  if (!publishedAt) {
    return null
  }

  const date = new Date(publishedAt)

  if (Number.isNaN(date.getTime())) {
    return null
  }

  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

const getGuideTopic = (guide: Pick<Guide, 'slug' | 'title'>) => {
  const text = `${guide.slug} ${guide.title}`.toLowerCase()

  if (text.includes('blind')) {
    return { label: 'Blind buy', marker: 'Checklist', tone: 'caution' }
  }

  if (text.includes('notes') || text.includes('pyramid') || text.includes('piramida')) {
    return { label: 'Notes', marker: 'Layer', tone: 'layer' }
  }

  if (text.includes('aroma') || text.includes('family') || text.includes('keluarga')) {
    return { label: 'Aroma', marker: 'Palette', tone: 'aroma' }
  }

  if (text.includes('konsentrasi') || text.includes('edp') || text.includes('edt')) {
    return { label: 'Konsentrasi', marker: 'Bottle', tone: 'intensity' }
  }

  if (text.includes('occasion') || text.includes('pakai') || text.includes('acara')) {
    return { label: 'Kebutuhan', marker: 'Moment', tone: 'occasion' }
  }

  return { label: 'Pemula', marker: 'Start', tone: 'beginner' }
}

export function GuideListPage({ onNavigate }: GuideListPageProps) {
  const cachedGuides = api.getCachedGuides()
  const [guides, setGuides] = useState<Guide[]>(cachedGuides?.data ?? [])
  const [isLoading, setIsLoading] = useState(!cachedGuides)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true
    const hadCachedGuides = Boolean(api.getCachedGuides())

    api
      .getGuides()
      .then((response) => {
        if (isMounted) {
          setGuides(response.data)
        }
      })
      .catch(() => {
        if (isMounted && !hadCachedGuides) {
          setError('Panduan belum bisa dimuat. Coba muat ulang halaman.')
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
    <main className="page guide-page">
      <section className="guide-hero">
        <p className="eyebrow">Panduan parfum</p>
        <h1>Belajar parfum dengan bahasa yang lebih sederhana</h1>
        <p>
          Baca istilah aroma, cara membaca notes, dan tips memilih parfum lokal
          supaya keputusan blind buy terasa lebih terarah.
        </p>
      </section>

      {isLoading ? (
        <LoadingBlock />
      ) : error ? (
        <ErrorBlock title="Panduan belum tersedia" message={error} />
      ) : guides.length > 0 ? (
        <section className="guide-list" aria-label="Daftar panduan parfum">
          {guides.map((guide, index) => {
            const guidePath = `/guides/${guide.slug}`
            const publishedDate = formatPublishedDate(guide.published_at)
            const topic = getGuideTopic(guide)

            return (
              <article className="guide-list-item" key={guide.slug}>
                <div className={`guide-list-item__visual guide-topic--${topic.tone}`}>
                  <span>{String(index + 1).padStart(2, '0')}</span>
                  <strong>{topic.marker}</strong>
                </div>
                <div className="guide-list-item__content">
                  <div className="guide-list-item__meta">
                    <span>{topic.label}</span>
                    <span>Cocok untuk pemula</span>
                  </div>
                  <h2>{guide.title}</h2>
                  <p>
                    {guide.summary ??
                      'Ringkasan panduan belum tersedia. Buka artikel untuk membaca isi lengkapnya.'}
                  </p>
                  {publishedDate ? <p className="guide-meta">{publishedDate}</p> : null}
                  <a
                    className="button button--primary"
                    href={guidePath}
                    onClick={(event) => preventAndNavigate(event, guidePath, onNavigate)}
                  >
                    Baca panduan
                  </a>
                </div>
              </article>
            )
          })}
        </section>
      ) : (
        <EmptyBlock
          title="Belum ada panduan"
          message="Panduan belum bisa ditampilkan saat ini. Coba kembali beberapa saat lagi."
        />
      )}
    </main>
  )
}
