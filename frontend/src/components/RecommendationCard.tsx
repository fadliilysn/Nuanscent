import { useEffect } from 'react'
import { TagBadge } from './TagBadge'
import { formatPriceRange } from '../lib/format'
import type { BlindBuyCautionLabel, Recommendation } from '../types/api'

type RecommendationCardProps = {
  recommendation: Recommendation
  rank: number
  detailReturnTo?: string
  isSelected?: boolean
  onNavigate: (to: string) => void
  onSelect?: () => void
}

const cautionToneClass = (label: BlindBuyCautionLabel) =>
  ({
    'Cenderung Aman': 'caution-badge--safe',
    'Perlu Pertimbangan': 'caution-badge--consider',
    'Sebaiknya Coba Sample Dulu': 'caution-badge--sample',
    'Data Belum Cukup': 'caution-badge--limited',
  })[label]

function ReasonList({ items }: { items: string[] }) {
  return (
    <ul className="reason-list">
      {items.map((item) => (
        <li key={item}>{item}</li>
      ))}
    </ul>
  )
}

function CautionBadge({ label }: { label: BlindBuyCautionLabel }) {
  return <span className={`caution-badge ${cautionToneClass(label)}`}>{label}</span>
}

export function RecommendationCard({
  recommendation,
  rank,
  detailReturnTo,
  isSelected = false,
  onNavigate,
  onSelect,
}: RecommendationCardProps) {
  const isTopPick = rank === 1
  const detailPath = detailReturnTo
    ? `/parfum/${recommendation.slug}?returnTo=${encodeURIComponent(detailReturnTo)}`
    : `/parfum/${recommendation.slug}`

  return (
    <article
      className={`recommendation-card ${isTopPick ? 'recommendation-card--top' : ''} ${isSelected ? 'recommendation-card--selected' : ''}`}
    >
      <div className="recommendation-card__media">
        {recommendation.image_url ? (
          <img
            src={recommendation.image_url}
            alt={`Botol parfum ${recommendation.name}`}
            loading="lazy"
            decoding="async"
          />
        ) : (
          <span>{recommendation.name.slice(0, 1).toUpperCase()}</span>
        )}
      </div>

      <div className="recommendation-card__body">
        <div className="recommendation-card__header">
          <div>
            <div className="recommendation-card__rank-line">
              <p className="eyebrow">Rekomendasi #{rank}</p>
              {isTopPick ? (
                <span className="recommendation-card__top-badge">
                  Rekomendasi terbaik
                </span>
              ) : null}
            </div>
            <h2>{recommendation.name}</h2>
            <p className="recommendation-card__brand">
              {recommendation.brand?.name ?? 'Brand belum tersedia'}
            </p>
          </div>
          <div className="match-meter" aria-label={`${recommendation.match_percentage}% cocok`}>
            <strong>{recommendation.match_percentage}%</strong>
            <span>cocok</span>
          </div>
        </div>

        <div className="recommendation-card__meta">
          {recommendation.main_aroma_category ? (
            <TagBadge tone={isTopPick ? 'yellow' : 'lavender'}>
              {recommendation.main_aroma_category.name}
            </TagBadge>
          ) : null}
          <span>{formatPriceRange(recommendation.price_min, recommendation.price_max)}</span>
        </div>

        <button
          className="button button--primary recommendation-card__cta"
          type="button"
          onClick={() => onNavigate(detailPath)}
        >
          Lihat detail parfum
        </button>

        <button
          className="button button--ghost recommendation-card__reason-button"
          type="button"
          aria-pressed={isSelected}
          onClick={onSelect}
        >
          Kenapa cocok?
        </button>
      </div>
    </article>
  )
}

type RecommendationReasonModalProps = {
  recommendation: Recommendation
  detailReturnTo?: string
  onClose: () => void
  onNavigate: (to: string) => void
}

export function RecommendationReasonModal({
  recommendation,
  detailReturnTo,
  onClose,
  onNavigate,
}: RecommendationReasonModalProps) {
  const detailPath = detailReturnTo
    ? `/parfum/${recommendation.slug}?returnTo=${encodeURIComponent(detailReturnTo)}`
    : `/parfum/${recommendation.slug}`

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onClose()
      }
    }

    window.addEventListener('keydown', handleKeyDown)

    return () => window.removeEventListener('keydown', handleKeyDown)
  }, [onClose])

  return (
    <div
      className="recommendation-reason-modal"
      role="presentation"
      onMouseDown={(event) => {
        if (event.target === event.currentTarget) {
          onClose()
        }
      }}
    >
      <section
        className="recommendation-reason-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="recommendation-reason-title"
      >
        <div className="recommendation-reason-modal__header">
          <div>
            <p className="eyebrow">Kenapa cocok?</p>
            <h2 id="recommendation-reason-title">{recommendation.name}</h2>
            <p>{recommendation.brand?.name ?? 'Brand belum tersedia'}</p>
          </div>
          <button
            className="recommendation-reason-modal__close"
            type="button"
            aria-label="Tutup alasan rekomendasi"
            onClick={onClose}
          >
            Tutup
          </button>
        </div>

        <div className="recommendation-reason-modal__summary">
          <div className="match-meter" aria-label={`${recommendation.match_percentage}% cocok`}>
            <strong>{recommendation.match_percentage}%</strong>
            <span>cocok</span>
          </div>
          {recommendation.main_aroma_category ? (
            <TagBadge tone="yellow">{recommendation.main_aroma_category.name}</TagBadge>
          ) : null}
        </div>

        <div className="recommendation-reason-modal__body">
          {recommendation.matched_reasons.length > 0 ? (
            <section className="recommendation-card__section">
              <h3>Alasan utama</h3>
              <ReasonList items={recommendation.matched_reasons} />
            </section>
          ) : null}

          <section className="recommendation-card__section recommendation-card__section--caution">
            <div className="recommendation-card__section-title">
              <h3>Panduan blind buy</h3>
              <CautionBadge label={recommendation.blind_buy_caution.label} />
            </div>
            <ReasonList items={recommendation.blind_buy_caution.reasons} />
          </section>

          {recommendation.data_limitations.length > 0 ? (
            <section className="recommendation-card__section recommendation-card__section--limited">
              <h3>Catatan data</h3>
              <ReasonList items={recommendation.data_limitations} />
            </section>
          ) : null}
        </div>

        <button
          className="button button--primary recommendation-reason-modal__cta"
          type="button"
          onClick={() => {
            onClose()
            onNavigate(detailPath)
          }}
        >
          Lihat detail parfum
        </button>
      </section>
    </div>
  )
}
