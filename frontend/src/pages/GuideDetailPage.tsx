import { useEffect, useMemo, useState } from 'react'
import { ErrorBlock, LoadingBlock } from '../components/StateBlock'
import { api } from '../lib/api'
import type { Guide } from '../types/api'

type GuideDetailPageProps = {
  slug: string
  onNavigate: (to: string) => void
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

const splitBodyIntoParagraphs = (body?: string) =>
  (body ?? '')
    .split(/\n{2,}/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean)

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

export function GuideDetailPage({ slug, onNavigate }: GuideDetailPageProps) {
  const cachedGuide = api.getCachedGuide(slug)
  const [guide, setGuide] = useState<Guide | null>(cachedGuide?.data ?? null)
  const [isLoading, setIsLoading] = useState(!cachedGuide)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true
    const cachedResponse = api.getCachedGuide(slug)

    Promise.resolve().then(() => {
      if (!isMounted) {
        return
      }

      if (cachedResponse) {
        setGuide(cachedResponse.data)
        setIsLoading(false)
      } else {
        setIsLoading(true)
      }

      setError(null)
    })

    api
      .getGuide(slug)
      .then((response) => {
        if (isMounted) {
          setGuide(response.data)
          setError(null)
        }
      })
      .catch(() => {
        if (isMounted && !cachedResponse) {
          setError('Panduan ini belum bisa ditampilkan saat ini.')
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

  const publishedDate = formatPublishedDate(guide?.published_at ?? null)
  const bodyParagraphs = useMemo(() => splitBodyIntoParagraphs(guide?.body), [guide?.body])
  const quickSummary =
    guide?.summary ?? bodyParagraphs[0] ?? 'Isi ringkas panduan belum tersedia.'
  const topic = guide ? getGuideTopic(guide) : null

  if (isLoading) {
    return (
      <main className="page page--compact">
        <LoadingBlock
          title="Sedang memuat panduan"
          message="Kami sedang menyiapkan isi panduan ini agar nyaman dibaca."
        />
      </main>
    )
  }

  if (error || !guide) {
    return (
      <main className="page page--compact">
        <ErrorBlock
          title="Panduan belum tersedia"
          message={error ?? 'Panduan ini belum bisa ditampilkan.'}
          actionLabel="Kembali ke panduan"
          onAction={() => onNavigate('/guides')}
        />
      </main>
    )
  }

  return (
    <main className="page guide-page guide-page--detail">
      <button className="back-link" type="button" onClick={() => onNavigate('/guides')}>
        Kembali ke panduan
      </button>

      <article className="guide-article">
        <header className="guide-article__header">
          <div className={`guide-detail-topic guide-topic--${topic?.tone ?? 'beginner'}`}>
            <span>{topic?.marker ?? 'Start'}</span>
            <strong>{topic?.label ?? 'Panduan'}</strong>
          </div>
          <div>
            <p className="eyebrow">Artikel parfum</p>
            <h1>{guide.title}</h1>
            {guide.summary ? <p className="guide-article__summary">{guide.summary}</p> : null}
            {publishedDate ? <p className="guide-meta">Terbit {publishedDate}</p> : null}
          </div>
        </header>

        <section className="guide-quick-summary">
          <p className="eyebrow">Ringkasan cepat</p>
          <p>{quickSummary}</p>
        </section>

        <div className="guide-article__body">
          {bodyParagraphs.length > 0 ? (
            bodyParagraphs.map((paragraph, index) => (
              <FragmentedParagraph
                key={`guide-paragraph-${index}`}
                paragraph={paragraph}
                index={index}
              />
            ))
          ) : (
            <p>Isi panduan belum tersedia.</p>
          )}
        </div>

        <section className="guide-next-step">
          <div>
            <p className="eyebrow">Baca panduan lainnya</p>
            <h2>Lanjut eksplorasi dengan topik lain.</h2>
            <p>
              Kembali ke daftar panduan untuk membaca istilah dan tips parfum lain
              dengan urutan yang lebih santai.
            </p>
          </div>
          <button
            className="button button--secondary"
            type="button"
            onClick={() => onNavigate('/guides')}
          >
            Kembali ke panduan
          </button>
        </section>
      </article>
    </main>
  )
}

function FragmentedParagraph({
  paragraph,
  index,
}: {
  paragraph: string
  index: number
}) {
  const shouldHighlight = index > 0 && (index + 1) % 4 === 0

  if (shouldHighlight) {
    return (
      <aside className="guide-article-callout">
        <span>Catatan</span>
        <p>{paragraph}</p>
      </aside>
    )
  }

  return <p>{paragraph}</p>
}
