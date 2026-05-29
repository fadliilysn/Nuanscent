type StateBlockProps = {
  title: string
  message: string
  actionLabel?: string
  onAction?: () => void
}

export function LoadingBlock() {
  return (
    <section className="state-block" aria-live="polite">
      <span className="state-block__marker"></span>
      <h2>Sedang memuat pilihan parfum</h2>
      <p>Kami sedang menyiapkan pilihan yang paling pas untuk kamu lihat.</p>
    </section>
  )
}

export function EmptyBlock({
  title,
  message,
  actionLabel,
  onAction,
}: StateBlockProps) {
  return (
    <section className="state-block">
      <span className="state-block__marker state-block__marker--quiet"></span>
      <h2>{title}</h2>
      <p>{message}</p>
      {actionLabel && onAction ? (
        <button className="button button--secondary" type="button" onClick={onAction}>
          {actionLabel}
        </button>
      ) : null}
    </section>
  )
}

export function ErrorBlock({
  title,
  message,
  actionLabel = 'Coba lagi',
  onAction,
}: StateBlockProps) {
  return (
    <section className="state-block state-block--error" role="alert">
      <span className="state-block__marker state-block__marker--error"></span>
      <h2>{title}</h2>
      <p>{message}</p>
      {onAction ? (
        <button className="button button--secondary" type="button" onClick={onAction}>
          {actionLabel}
        </button>
      ) : null}
    </section>
  )
}
