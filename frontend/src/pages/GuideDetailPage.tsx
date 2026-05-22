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

export function GuideDetailPage({ slug, onNavigate }: GuideDetailPageProps) {
  const [guide, setGuide] = useState<Guide | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true

    api
      .getGuide(slug)
      .then((response) => {
        if (isMounted) {
          setGuide(response.data)
        }
      })
      .catch(() => {
        if (isMounted) {
          setError('Panduan ini belum tersedia atau belum dipublikasikan.')
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

  if (isLoading) {
    return (
      <main className="page page--compact">
        <LoadingBlock />
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
          <p className="eyebrow">Artikel parfum</p>
          <h1>{guide.title}</h1>
          {guide.summary ? <p className="guide-article__summary">{guide.summary}</p> : null}
          {publishedDate ? <p className="guide-meta">Dipublikasikan {publishedDate}</p> : null}
        </header>

        <div className="guide-article__body">
          {bodyParagraphs.length > 0 ? (
            bodyParagraphs.map((paragraph) => <p key={paragraph}>{paragraph}</p>)
          ) : (
            <p>Isi panduan belum tersedia.</p>
          )}
        </div>
      </article>
    </main>
  )
}
