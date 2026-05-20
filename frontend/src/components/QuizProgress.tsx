type QuizProgressProps = {
  currentStep: number
  totalSteps: number
}

export function QuizProgress({ currentStep, totalSteps }: QuizProgressProps) {
  const progress = Math.round((currentStep / totalSteps) * 100)

  return (
    <div className="quiz-progress" aria-label={`Langkah ${currentStep} dari ${totalSteps}`}>
      <div className="quiz-progress__meta">
        <span>
          Langkah {currentStep} / {totalSteps}
        </span>
        <strong>{progress}%</strong>
      </div>
      <div className="quiz-progress__track" aria-hidden="true">
        <span style={{ width: `${progress}%` }} />
      </div>
    </div>
  )
}
