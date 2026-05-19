import { useEffect, useMemo, useState } from 'react'
import { EmptyBlock, ErrorBlock, LoadingBlock } from '../components/StateBlock'
import { TagBadge } from '../components/TagBadge'
import { api } from '../lib/api'
import { formatOptional, formatPriceRange, formatVolume } from '../lib/format'
import type { Note, NotePosition, Perfume } from '../types/api'

type PerfumeDetailPageProps = {
  slug: string
  onNavigate: (to: string) => void
}

const noteSections: Array<{ key: NotePosition; label: string }> = [
  { key: 'top', label: 'Top notes' },
  { key: 'middle', label: 'Middle notes' },
  { key: 'base', label: 'Base notes' },
]

const aromaTagToneBySlug: Record<string, string> = {
  citrus: 'sunny',
  fruity: 'sunny',
  aquatic: 'blue',
  clean: 'blue',
  soapy: 'blue',
  tea: 'green',
  rose: 'floral',
  jasmine: 'floral',
  'white-floral': 'floral',
  floral: 'floral',
  vanilla: 'gourmand',
  caramel: 'gourmand',
  coffee: 'gourmand',
  creamy: 'gourmand',
  cedar: 'earthy',
  sandalwood: 'earthy',
  vetiver: 'earthy',
  patchouli: 'earthy',
  woody: 'earthy',
  amber: 'amber',
  spicy: 'amber',
  saffron: 'amber',
  musky: 'soft',
  powdery: 'soft',
  smoky: 'dark',
  leathery: 'dark',
  tobacco: 'dark',
  oud: 'dark',
}

const aromaTagToneClass = (slug: string) =>
  `aroma-chip--${aromaTagToneBySlug[slug] ?? 'neutral'}`

const groupNotes = (notes: Note[] = []) =>
  notes.reduce<Record<NotePosition, Note[]>>(
    (groups, note) => {
      const position = note.position ?? 'unspecified'
      groups[position].push(note)

      return groups
    },
    {
      top: [],
      middle: [],
      base: [],
      unspecified: [],
    },
  )

export function PerfumeDetailPage({ slug, onNavigate }: PerfumeDetailPageProps) {
  const [perfume, setPerfume] = useState<Perfume | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true

    api
      .getPerfume(slug)
      .then((response) => {
        if (isMounted) {
          setPerfume(response.data)
        }
      })
      .catch(() => {
        if (isMounted) {
          setError('Detail parfum tidak ditemukan atau belum dipublish.')
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

  const groupedNotes = useMemo(() => groupNotes(perfume?.notes), [perfume?.notes])
  const visibleNoteSections = useMemo(
    () =>
      groupedNotes.unspecified.length > 0
        ? [...noteSections, { key: 'unspecified' as const, label: 'Notes tanpa posisi' }]
        : noteSections,
    [groupedNotes],
  )
  const hasSource =
    perfume?.source.name || perfume?.source.url || perfume?.source.last_verified_at

  if (isLoading) {
    return (
      <main className="page page--compact">
        <LoadingBlock />
      </main>
    )
  }

  if (error || !perfume) {
    return (
      <main className="page page--compact">
        <ErrorBlock
          title="Detail belum tersedia"
          message={error ?? 'Parfum ini belum bisa ditampilkan.'}
          actionLabel="Kembali ke katalog"
          onAction={() => onNavigate('/parfum')}
        />
      </main>
    )
  }

  return (
    <main className="page detail-page">
      <button className="back-link" type="button" onClick={() => onNavigate('/parfum')}>
        Kembali ke katalog
      </button>

      <section className="detail-hero">
        <div className="detail-hero__media">
          {perfume.image_url ? (
            <img
              src={perfume.image_url}
              alt={`Botol parfum ${perfume.name}`}
              loading="lazy"
            />
          ) : (
            <span>{perfume.name.slice(0, 1).toUpperCase()}</span>
          )}
        </div>

        <div className="detail-hero__content">
          <p className="eyebrow">{perfume.brand?.name ?? 'Brand belum tersedia'}</p>
          <h1>{perfume.name}</h1>
          <div className="detail-hero__badges">
            {perfume.main_aroma_category ? (
              <TagBadge tone="yellow">{perfume.main_aroma_category.name}</TagBadge>
            ) : null}
            {perfume.marketed_gender ? (
              <TagBadge tone="blue">{perfume.marketed_gender}</TagBadge>
            ) : null}
            {perfume.intensity ? (
              <TagBadge tone="mint">{perfume.intensity}</TagBadge>
            ) : null}
          </div>
          <p className="detail-summary">
            {perfume.short_description ?? 'Ringkasan parfum belum tersedia.'}
          </p>
        </div>
      </section>

      <section className="detail-grid">
        <article className="info-panel info-panel--wide">
          <p className="eyebrow">Deskripsi resmi</p>
          <p>
            {perfume.official_description ??
              'Deskripsi resmi belum tersedia dari sumber data.'}
          </p>
        </article>

        <article className="info-panel">
          <p className="eyebrow">Detail produk</p>
          <dl className="detail-list">
            <div>
              <dt>Harga</dt>
              <dd>{formatPriceRange(perfume.price_min, perfume.price_max)}</dd>
            </div>
            <div>
              <dt>Konsentrasi</dt>
              <dd>{formatOptional(perfume.concentration)}</dd>
            </div>
            <div>
              <dt>Volume</dt>
              <dd>{formatVolume(perfume.volume_ml)}</dd>
            </div>
          </dl>
        </article>

        <article className="info-panel">
          <p className="eyebrow">Tag aroma</p>
          <div className="aroma-tag-list" aria-label="Daftar tag aroma">
            {perfume.aroma_tags && perfume.aroma_tags.length > 0 ? (
              perfume.aroma_tags.map((tag) => (
                <span
                  className={`aroma-chip ${aromaTagToneClass(tag.slug)}`}
                  key={tag.slug}
                >
                  {tag.name}
                </span>
              ))
            ) : (
              <span className="muted-text">Tag aroma belum tersedia.</span>
            )}
          </div>
        </article>

        <article className="info-panel">
          <p className="eyebrow">Occasion</p>
          <div className="badge-list">
            {perfume.occasions && perfume.occasions.length > 0 ? (
              perfume.occasions.map((occasion) => (
                <TagBadge key={occasion.slug} tone="mint">
                  {occasion.name}
                </TagBadge>
              ))
            ) : (
              <span className="muted-text">Occasion belum tersedia.</span>
            )}
          </div>
        </article>

        <article className="info-panel info-panel--wide">
          <p className="eyebrow">Notes pyramid</p>
          <div className="notes-grid">
            {visibleNoteSections.map((section) => (
              <div className="note-column" key={section.key}>
                <h2>{section.label}</h2>
                {groupedNotes[section.key].length > 0 ? (
                  <ul>
                    {groupedNotes[section.key].map((note) => (
                      <li key={`${section.key}-${note.slug}`}>
                        <strong>{note.name}</strong>
                        {note.description_simple ? <span>{note.description_simple}</span> : null}
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p className="muted-text">Belum ada data.</p>
                )}
              </div>
            ))}
          </div>
        </article>

        {hasSource ? (
          <article className="info-panel info-panel--source">
            <p className="eyebrow">Sumber data</p>
            <p>
              {perfume.source.url ? (
                <a href={perfume.source.url} target="_blank" rel="noreferrer">
                  {perfume.source.name ?? perfume.source.url}
                </a>
              ) : (
                perfume.source.name
              )}
            </p>
            {perfume.source.last_verified_at ? (
              <small>Terakhir diverifikasi: {perfume.source.last_verified_at}</small>
            ) : null}
          </article>
        ) : null}
      </section>

      {!perfume ? (
        <EmptyBlock
          title="Data kosong"
          message="Parfum ini belum memiliki data publik yang bisa ditampilkan."
        />
      ) : null}
    </main>
  )
}
