import { formatPriceRange } from '../lib/format'
import type { Perfume } from '../types/api'
import { TagBadge } from './TagBadge'

type PerfumeCardProps = {
  perfume: Perfume
  detailReturnTo?: string
  onNavigate: (to: string) => void
}

export function PerfumeCard({
  perfume,
  detailReturnTo,
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
      </div>
    </article>
  )
}
