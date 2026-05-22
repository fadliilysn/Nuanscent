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

export function GuideListPage({ onNavigate }: GuideListPageProps) {
  const [guides, setGuides] = useState<Guide[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true

    api
      .getGuides()
      .then((response) => {
        if (isMounted) {
          setGuides(response.data)
        }
      })
      .catch(() => {
        if (isMounted) {
          setError('Panduan belum bisa dimuat. Pastikan API Laravel aktif.')
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
        <h1>Belajar parfum dengan bahasa yang lebih sederhana.</h1>
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
          {guides.map((guide) => {
            const guidePath = `/guides/${guide.slug}`
            const publishedDate = formatPublishedDate(guide.published_at)

            return (
              <article className="guide-list-item" key={guide.slug}>
                <div>
                  {publishedDate ? <p className="guide-meta">{publishedDate}</p> : null}
                  <h2>{guide.title}</h2>
                  <p>
                    {guide.summary ??
                      'Ringkasan panduan belum tersedia. Buka artikel untuk membaca isi lengkapnya.'}
                  </p>
                </div>
                <a
                  className="button button--primary"
                  href={guidePath}
                  onClick={(event) => preventAndNavigate(event, guidePath, onNavigate)}
                >
                  Baca panduan
                </a>
              </article>
            )
          })}
        </section>
      ) : (
        <EmptyBlock
          title="Belum ada panduan"
          message="Belum ada panduan published yang bisa ditampilkan saat ini."
        />
      )}
    </main>
  )
}
