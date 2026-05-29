import { useEffect, useMemo, useState } from 'react'
import { EmptyBlock, ErrorBlock, LoadingBlock } from '../components/StateBlock'
import { TagBadge } from '../components/TagBadge'
import { api } from '../lib/api'
import { formatOptional, formatPriceRange, formatVolume } from '../lib/format'
import type { Note, NotePosition, Perfume } from '../types/api'

type PerfumeDetailPageProps = {
  slug: string
  returnTo?: string | null
  onNavigate: (to: string) => void
}

type NoteGroupKey = NotePosition

type NoteSection = {
  key: NoteGroupKey
  label: string
  helper: string
}

const noteSections: NoteSection[] = [
  {
    key: 'top',
    label: 'Top Notes',
    helper: 'Kesan pertama saat parfum baru disemprot.',
  },
  {
    key: 'middle',
    label: 'Middle Notes',
    helper: 'Karakter utama setelah aroma pembuka mulai mereda.',
  },
  {
    key: 'base',
    label: 'Base Notes',
    helper: 'Fondasi aroma yang terasa paling lama.',
  },
]

const unspecifiedNoteSection: NoteSection = {
  key: 'unspecified',
  label: 'Notes lainnya',
  helper: 'Notes tambahan yang belum memiliki posisi pyramid jelas.',
}

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

const normalizeNotePosition = (position?: string | null): NoteGroupKey =>
  position === 'top' || position === 'middle' || position === 'base'
    ? position
    : 'unspecified'

const groupNotes = (notes: Note[] = []) =>
  notes.reduce<Record<NoteGroupKey, Note[]>>(
    (groups, note) => {
      const position = normalizeNotePosition(note.position)
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

export function PerfumeDetailPage({
  slug,
  returnTo,
  onNavigate,
}: PerfumeDetailPageProps) {
  const cachedPerfume = api.getCachedPerfume(slug)
  const [perfume, setPerfume] = useState<Perfume | null>(cachedPerfume?.data ?? null)
  const [isLoading, setIsLoading] = useState(!cachedPerfume)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let isMounted = true
    const cachedResponse = api.getCachedPerfume(slug)

    Promise.resolve().then(() => {
      if (!isMounted) {
        return
      }

      if (cachedResponse) {
        setPerfume(cachedResponse.data)
        setIsLoading(false)
      } else {
        setIsLoading(true)
      }

      setError(null)
    })

    api
      .getPerfume(slug)
      .then((response) => {
        if (isMounted) {
          setPerfume(response.data)
          setError(null)
        }
      })
      .catch(() => {
        if (isMounted && !cachedResponse) {
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
  const hasNotes = useMemo(
    () => Object.values(groupedNotes).some((notes) => notes.length > 0),
    [groupedNotes],
  )
  const hasUnspecifiedNotes = groupedNotes.unspecified.length > 0
  const hasSource =
    perfume?.source.name || perfume?.source.url || perfume?.source.last_verified_at
  const hasVariants = Boolean(perfume?.variants && perfume.variants.length > 0)
  const backTarget = returnTo ?? '/parfum'
  const backLabel = returnTo?.startsWith('/quiz')
    ? 'Kembali ke hasil rekomendasi'
    : returnTo?.startsWith('/brands') || returnTo?.startsWith('/brands')
      ? 'Kembali ke halaman brands'
      : 'Kembali ke katalog'

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
          actionLabel={backLabel}
          onAction={() => onNavigate(backTarget)}
        />
      </main>
    )
  }

  return (
    <main className="page detail-page">
      <button className="back-link" type="button" onClick={() => onNavigate(backTarget)}>
        {backLabel}
      </button>

      <section className="detail-hero">
        <div className="detail-hero__media">
          {perfume.image_url ? (
            <img
              src={perfume.image_url}
              alt={`Botol parfum ${perfume.name}`}
              loading="lazy"
              decoding="async"
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
          <dl className="detail-list detail-list--metrics">
            <div>
              <dt>Harga</dt>
              <dd>{formatPriceRange(perfume.price_min, perfume.price_max)}</dd>
            </div>
            <div>
              <dt>Konsentrasi</dt>
              <dd>{formatOptional(perfume.concentration)}</dd>
            </div>
            {!hasVariants ? (
              <div>
                <dt>Volume</dt>
                <dd>{formatVolume(perfume.volume_ml)}</dd>
              </div>
            ) : null}
          </dl>
        </article>

        {hasVariants ? (
          <article className="info-panel">
            <p className="eyebrow">Pilihan ukuran</p>
            <div className="variant-list">
              {perfume.variants?.map((variant, index) => {
                const volumeLabel =
                  variant.volume_ml !== null ? formatVolume(variant.volume_ml) : null
                const title =
                  variant.label ??
                  volumeLabel ??
                  `Varian ${index + 1}`
                const shouldShowVolume =
                  variant.label !== null &&
                  volumeLabel !== null &&
                  variant.label.trim().toLowerCase() !== volumeLabel.toLowerCase()

                return (
                  <div className="variant-option" key={variant.id}>
                    <div>
                      <h2>{title}</h2>
                      {shouldShowVolume ? (
                        <span>{volumeLabel}</span>
                      ) : null}
                    </div>
                    <strong className={variant.price === null ? 'muted-text' : undefined}>
                      {formatPriceRange(variant.price, variant.price)}
                    </strong>
                  </div>
                )
              })}
            </div>
          </article>
        ) : null}

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
          {hasNotes ? (
            <div className="notes-pyramid" aria-label="Notes pyramid parfum">
              <div className="notes-pyramid__levels">
                {noteSections.map((section) => (
                  <div
                    className={`note-layer note-layer--${section.key}`}
                    key={section.key}
                  >
                    <div className="note-layer__header">
                      <span aria-hidden="true"></span>
                      <div>
                        <h2>{section.label}</h2>
                        <p>{section.helper}</p>
                      </div>
                    </div>
                    <div className="note-chip-list">
                      {groupedNotes[section.key].length > 0 ? (
                        groupedNotes[section.key].map((note) => (
                          <span className="note-chip" key={`${section.key}-${note.slug}`}>
                            <strong>{note.name}</strong>
                            {note.description_simple ? <small>{note.description_simple}</small> : null}
                          </span>
                        ))
                      ) : (
                        <span className="note-chip note-chip--empty">Belum ada data.</span>
                      )}
                    </div>
                  </div>
                ))}
              </div>
              {hasUnspecifiedNotes ? (
                <div className="note-layer note-layer--unspecified">
                  <div className="note-layer__header">
                    <span aria-hidden="true"></span>
                    <div>
                      <h2>{unspecifiedNoteSection.label}</h2>
                      <p>{unspecifiedNoteSection.helper}</p>
                    </div>
                  </div>
                  <div className="note-chip-list">
                    {groupedNotes.unspecified.map((note) => (
                      <span className="note-chip" key={`unspecified-${note.slug}`}>
                        <strong>{note.name}</strong>
                        {note.description_simple ? <small>{note.description_simple}</small> : null}
                      </span>
                    ))}
                  </div>
                </div>
              ) : null}
            </div>
          ) : (
            <p className="muted-text">Notes belum tersedia.</p>
          )}
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
