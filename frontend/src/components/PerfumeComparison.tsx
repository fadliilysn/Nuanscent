import { useEffect, useId, useRef, useState } from 'react'
import type { CSSProperties, ReactNode } from 'react'
import type { ComparePerfumeItem } from '../hooks/useComparePerfumes'
import { api } from '../lib/api'
import { formatPriceRange, formatVolume } from '../lib/format'
import type { NotePosition, Perfume } from '../types/api'

type CompareBarProps = {
  items: ComparePerfumeItem[]
  maxItems: number
  onCompare: () => void
  onRemove: (slug: string) => void
  onClear: () => void
}

export function CompareBar({
  items,
  maxItems,
  onCompare,
  onRemove,
  onClear,
}: CompareBarProps) {
  const canCompare = items.length >= 2

  return (
    <aside className="compare-bar" aria-label="Pilihan parfum untuk dibandingkan">
      <div className="compare-bar__inner">
        <div className="compare-bar__summary">
          <strong>
            {items.length}/{maxItems} parfum dipilih
          </strong>
          <span>
            {canCompare
              ? 'Pilihanmu siap dibandingkan.'
              : 'Pilih 1 parfum lagi untuk membandingkan.'}
          </span>
        </div>

        <div className="compare-bar__items">
          {items.map((item) => (
            <div className="compare-bar__item" key={item.slug}>
              <CompareImage item={item} />
              <span>{item.name}</span>
              <button
                type="button"
                aria-label={`Hapus ${item.name} dari perbandingan`}
                onClick={() => onRemove(item.slug)}
              >
                ×
              </button>
            </div>
          ))}
        </div>

        <div className="compare-bar__actions">
          <button className="button button--ghost" type="button" onClick={onClear}>
            Kosongkan
          </button>
          <button
            className="button button--primary"
            type="button"
            disabled={!canCompare}
            onClick={onCompare}
          >
            Bandingkan
          </button>
        </div>
      </div>
    </aside>
  )
}

type CompareModalProps = {
  items: ComparePerfumeItem[]
  returnTo: string
  onClose: () => void
  onRemove: (slug: string) => void
  onNavigate: (to: string) => void
}

type DetailState = {
  perfume: Perfume | null
  isLoading: boolean
  hasError: boolean
}

export function CompareModal({
  items,
  returnTo,
  onClose,
  onRemove,
  onNavigate,
}: CompareModalProps) {
  const titleId = useId()
  const closeButtonRef = useRef<HTMLButtonElement>(null)
  const [details, setDetails] = useState<Record<string, DetailState>>(() =>
    Object.fromEntries(
      items.map((item) => {
        const cachedPerfume = api.getCachedPerfume(item.slug)?.data ?? null

        return [
          item.slug,
          {
            perfume: cachedPerfume,
            isLoading: !cachedPerfume,
            hasError: false,
          },
        ]
      }),
    ),
  )

  useEffect(() => {
    const previousOverflow = document.body.style.overflow
    const previousActiveElement = document.activeElement as HTMLElement | null

    document.body.style.overflow = 'hidden'
    closeButtonRef.current?.focus()

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onClose()
      }
    }

    window.addEventListener('keydown', handleKeyDown)

    return () => {
      document.body.style.overflow = previousOverflow
      window.removeEventListener('keydown', handleKeyDown)
      previousActiveElement?.focus()
    }
  }, [onClose])

  useEffect(() => {
    let isMounted = true

    items.forEach((item) => {
      api
        .getPerfume(item.slug)
        .then((response) => {
          if (isMounted) {
            setDetails((currentDetails) => ({
              ...currentDetails,
              [item.slug]: {
                perfume: response.data,
                isLoading: false,
                hasError: false,
              },
            }))
          }
        })
        .catch(() => {
          if (isMounted) {
            setDetails((currentDetails) => ({
              ...currentDetails,
              [item.slug]: {
                perfume: currentDetails[item.slug]?.perfume ?? null,
                isLoading: false,
                hasError: true,
              },
            }))
          }
        })
    })

    return () => {
      isMounted = false
    }
  }, [items])

  return (
    <div
      className="compare-modal"
      role="presentation"
      onMouseDown={(event) => {
        if (event.target === event.currentTarget) {
          onClose()
        }
      }}
    >
      <section
        className="compare-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby={titleId}
      >
        <header className="compare-modal__header">
          <div>
            <p className="eyebrow">Bandingkan parfum</p>
            <h2 id={titleId}>Lihat perbedaan pilihanmu</h2>
            <p>Bandingkan aroma, harga, notes, dan kegunaan sebelum memilih.</p>
          </div>
          <button
            ref={closeButtonRef}
            className="compare-modal__close"
            type="button"
            aria-label="Tutup perbandingan parfum"
            onClick={onClose}
          >
            Tutup
          </button>
        </header>

        <div
          className="compare-modal__grid"
          style={{ '--compare-count': items.length } as CSSProperties}
        >
          {items.map((item) => (
            <ComparisonColumn
              key={item.slug}
              item={item}
              detailState={details[item.slug]}
              onRemove={() => onRemove(item.slug)}
              onNavigate={() => {
                onClose()
                onNavigate(
                  `/parfum/${item.slug}?returnTo=${encodeURIComponent(returnTo)}`,
                )
              }}
            />
          ))}
        </div>
      </section>
    </div>
  )
}

function ComparisonColumn({
  item,
  detailState,
  onRemove,
  onNavigate,
}: {
  item: ComparePerfumeItem
  detailState?: DetailState
  onRemove: () => void
  onNavigate: () => void
}) {
  const perfume = detailState?.perfume
  const isWaitingForDetail = !detailState || (detailState.isLoading && !perfume)

  return (
    <article className="compare-column">
      <div className="compare-column__hero">
        <CompareImage item={item} large />
        <div>
          <p>{perfume?.brand?.name ?? item.brandName}</p>
          <h3>{perfume?.name ?? item.name}</h3>
        </div>
        <button type="button" onClick={onRemove}>
          Hapus
        </button>
      </div>

      {isWaitingForDetail ? (
        <p className="compare-column__status" aria-live="polite">
          Menyiapkan detail parfum...
        </p>
      ) : null}

      {detailState?.hasError ? (
        <p className="compare-column__status compare-column__status--error">
          Sebagian detail belum bisa dimuat.
        </p>
      ) : null}

      {!isWaitingForDetail ? (
        <>
          <CompareField label="Kategori aroma">
            {perfume?.main_aroma_category?.name ??
              item.aromaCategoryName ??
              'Belum tersedia'}
          </CompareField>

          <CompareField label="Harga">
            {formatPriceRange(
              perfume?.price_min ?? item.priceMin,
              perfume?.price_max ?? item.priceMax,
            )}
          </CompareField>

          <CompareField label="Pilihan ukuran">
            {perfume?.variants && perfume.variants.length > 0 ? (
              <ul className="compare-list">
                {perfume.variants.map((variant, index) => {
                  const volumeLabel =
                    variant.volume_ml !== null ? formatVolume(variant.volume_ml) : null
                  const primaryLabel =
                    variant.label ?? volumeLabel ?? `Varian ${index + 1}`
                  const showVolume =
                    variant.label &&
                    volumeLabel &&
                    variant.label.trim().toLowerCase() !== volumeLabel.toLowerCase()

                  return (
                    <li key={variant.id}>
                      <span>
                        {primaryLabel}
                        {showVolume ? ` · ${volumeLabel}` : ''}
                      </span>
                      <strong>{formatPriceRange(variant.price, variant.price)}</strong>
                    </li>
                  )
                })}
              </ul>
            ) : (
              'Variant belum tersedia'
            )}
          </CompareField>

          <CompareField label="Tag aroma">
            {perfume?.aroma_tags && perfume.aroma_tags.length > 0 ? (
              <div className="compare-chip-list">
                {perfume.aroma_tags.map((tag) => (
                  <span key={tag.slug}>{tag.name}</span>
                ))}
              </div>
            ) : (
              'Belum tersedia'
            )}
          </CompareField>

          <CompareField label="Notes">
            {perfume?.notes && perfume.notes.length > 0 ? (
              <CompareNotes perfume={perfume} />
            ) : (
              'Notes belum tersedia'
            )}
          </CompareField>

          <CompareField label="Cocok untuk">
            {perfume?.occasions && perfume.occasions.length > 0 ? (
              <div className="compare-chip-list">
                {perfume.occasions.map((occasion) => (
                  <span key={occasion.slug}>{occasion.name}</span>
                ))}
              </div>
            ) : (
              'Belum tersedia'
            )}
          </CompareField>

          <CompareField label="Ringkasan">
            {perfume?.short_description ??
              item.shortDescription ??
              'Deskripsi singkat belum tersedia.'}
          </CompareField>
        </>
      ) : null}

      <button
        className="button button--primary compare-column__detail"
        type="button"
        onClick={onNavigate}
      >
        Lihat detail parfum
      </button>
    </article>
  )
}

function CompareField({
  label,
  children,
}: {
  label: string
  children: ReactNode
}) {
  return (
    <section className="compare-field">
      <h4>{label}</h4>
      <div>{children}</div>
    </section>
  )
}

function CompareNotes({ perfume }: { perfume: Perfume }) {
  const positions: Array<{ key: NotePosition; label: string }> = [
    { key: 'top', label: 'Top' },
    { key: 'middle', label: 'Middle' },
    { key: 'base', label: 'Base' },
    { key: 'unspecified', label: 'Lainnya' },
  ]

  return (
    <div className="compare-notes">
      {positions.map(({ key, label }) => {
        const notes = perfume.notes?.filter(
          (note) => (note.position ?? 'unspecified') === key,
        )

        return notes && notes.length > 0 ? (
          <div key={key}>
            <strong>{label}</strong>
            <span>{notes.map((note) => note.name).join(', ')}</span>
          </div>
        ) : null
      })}
    </div>
  )
}

function CompareImage({
  item,
  large = false,
}: {
  item: ComparePerfumeItem
  large?: boolean
}) {
  return (
    <div className={`compare-image ${large ? 'compare-image--large' : ''}`}>
      {item.imageUrl ? (
        <img
          src={item.imageUrl}
          alt={`Botol parfum ${item.name}`}
          loading="lazy"
          decoding="async"
        />
      ) : (
        <span>{item.name.slice(0, 1).toUpperCase()}</span>
      )}
    </div>
  )
}
