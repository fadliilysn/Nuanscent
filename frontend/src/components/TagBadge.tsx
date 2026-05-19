type TagBadgeProps = {
  children: string
  tone?: 'mint' | 'yellow' | 'coral' | 'lavender' | 'blue' | 'neutral'
}

export function TagBadge({ children, tone = 'neutral' }: TagBadgeProps) {
  return <span className={`tag-badge tag-badge--${tone}`}>{children}</span>
}
