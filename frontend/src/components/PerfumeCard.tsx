import { formatPriceRange } from '../lib/format'
import type { Perfume } from '../types/api'
import { TagBadge } from './TagBadge'

type PerfumeCardProps = {
  perfume: Perfume
  detailReturnTo?: string
  isCompareSelected?: boolean
  isCompareDisabled?: boolean
  onToggleCompare?: () => void
  onNavigate: (to: string) => void
}

export function PerfumeCard({
  perfume,
  detailReturnTo,
  isCompareSelected = false,
  isCompareDisabled = false,
  onToggleCompare,
  onNavigate,
}: PerfumeCardProps) {
  const detailPath = detailReturnTo
    ? `/parfum/${perfume.slug}?returnTo=${encodeURIComponent(detailReturnTo)}`
    : `/parfum/${perfume.slug}`

  return (
    <article className="perfume-card">
      <a
        className="perfume-card__media"
        href={detailPath}
        onClick={(event) => {
          event.preventDefault()
          onNavigate(detailPath)
        }}
        aria-label={`Lihat detail ${perfume.name}`}
      >
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
      </a>

      <div className="perfume-card__body">
        <div>
          <p className="eyebrow">{perfume.brand?.name ?? 'Brand belum tersedia'}</p>
          <h2>
            <a
              href={detailPath}
              onClick={(event) => {
                event.preventDefault()
                onNavigate(detailPath)
              }}
            >
              {perfume.name}
            </a>
          </h2>
        </div>

        {perfume.short_description ? (
          <p className="perfume-card__description">{perfume.short_description}</p>
        ) : (
          <p className="perfume-card__description perfume-card__description--muted">
            Deskripsi singkat belum tersedia.
          </p>
        )}

        <div className="perfume-card__meta">
          <TagBadge tone="yellow">
            {perfume.main_aroma_category?.name ?? 'Aroma belum dipilih'}
          </TagBadge>
          <span>{formatPriceRange(perfume.price_min, perfume.price_max)}</span>
        </div>

        {onToggleCompare ? (
          <div className="perfume-card__actions">
            <button
              className="button button--primary"
              type="button"
              onClick={() => onNavigate(detailPath)}
            >
              Lihat detail
            </button>
            <button
              className={`button button--ghost perfume-card__compare ${isCompareSelected ? 'perfume-card__compare--selected' : ''}`}
              type="button"
              aria-pressed={isCompareSelected}
              disabled={isCompareDisabled}
              title={
                isCompareDisabled
                  ? 'Maksimal 3 parfum untuk dibandingkan.'
                  : undefined
              }
              onClick={onToggleCompare}
            >
              {isCompareSelected
                ? 'Hapus dari banding'
                : isCompareDisabled
                  ? 'Maksimal 3'
                  : 'Bandingkan'}
            </button>
          </div>
        ) : null}
      </div>
    </article>
  )
}
