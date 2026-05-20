import { TagBadge } from './TagBadge'
import { formatPriceRange } from '../lib/format'
import type { BlindBuyCautionLabel, Recommendation } from '../types/api'

type RecommendationCardProps = {
  recommendation: Recommendation
  rank: number
  detailReturnTo?: string
  onNavigate: (to: string) => void
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
  onNavigate,
}: RecommendationCardProps) {
  const isTopPick = rank === 1
  const detailPath = detailReturnTo
    ? `/parfum/${recommendation.slug}?returnTo=${encodeURIComponent(detailReturnTo)}`
    : `/parfum/${recommendation.slug}`

  return (
    <article className={`recommendation-card ${isTopPick ? 'recommendation-card--top' : ''}`}>
      <div className="recommendation-card__media">
        {recommendation.image_url ? (
          <img
            src={recommendation.image_url}
            alt={`Botol parfum ${recommendation.name}`}
            loading="lazy"
          />
        ) : (
          <span>{recommendation.name.slice(0, 1).toUpperCase()}</span>
        )}
      </div>

      <div className="recommendation-card__body">
        <div className="recommendation-card__header">
          <div>
            <p className="eyebrow">Rekomendasi #{rank}</p>
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

        <details className="recommendation-card__details">
          <summary>Lihat alasan rekomendasi</summary>
          <div className="recommendation-card__details-body">
            {recommendation.matched_reasons.length > 0 ? (
              <section className="recommendation-card__section">
                <h3>Kenapa masuk rekomendasi</h3>
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
        </details>

        <button
          className="button button--primary recommendation-card__cta"
          type="button"
          onClick={() => onNavigate(detailPath)}
        >
          Lihat detail parfum
        </button>
      </div>
    </article>
  )
}
