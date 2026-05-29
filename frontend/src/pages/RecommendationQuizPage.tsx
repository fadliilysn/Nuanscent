import { useEffect, useMemo, useState, type ReactNode } from 'react'
import { QuizProgress } from '../components/QuizProgress'
import {
  RecommendationCard,
  RecommendationReasonModal,
} from '../components/RecommendationCard'
import { EmptyBlock, ErrorBlock } from '../components/StateBlock'
import { api } from '../lib/api'
import type {
  AromaCategory,
  AromaTag,
  BlindBuyComfort,
  IntensityPreference,
  MarketedGenderPreference,
  Occasion,
  Recommendation,
  RecommendationRequestPayload,
} from '../types/api'

type RecommendationQuizPageProps = {
  locationSearch: string
  onNavigate: (to: string) => void
}

type BudgetKey =
  | 'flexible'
  | 'under-100'
  | '100-200'
  | '200-350'
  | '350-500'
  | 'above-500'

type QuizState = {
  occasion: string
  aromaPreferences: string[]
  budget: BudgetKey
  intensityPreference: IntensityPreference
  avoidedTags: string[]
  blindBuyComfort: BlindBuyComfort | ''
  marketedGenderPreference: MarketedGenderPreference
}

type Choice<T extends string> = {
  value: T
  label: string
  helper: string
}

const totalSteps = 6
const quizStorageKey = 'nuanscent.latestRecommendationResults'

const initialQuizState: QuizState = {
  occasion: '',
  aromaPreferences: [],
  budget: 'flexible',
  intensityPreference: 'no_preference',
  avoidedTags: [],
  blindBuyComfort: '',
  marketedGenderPreference: 'no_preference',
}

const budgetChoices: Array<Choice<BudgetKey> & { priceMin: number | null; priceMax: number | null }> = [
  {
    value: 'flexible',
    label: 'Fleksibel dulu',
    helper: 'Tampilkan kandidat terbaik tanpa membatasi harga.',
    priceMin: null,
    priceMax: null,
  },
  {
    value: 'under-100',
    label: 'Di bawah Rp100 ribu',
    helper: 'Untuk eksplorasi ringan atau botol kecil.',
    priceMin: null,
    priceMax: 100000,
  },
  {
    value: '100-200',
    label: 'Rp100 ribu - Rp200 ribu',
    helper: 'Rentang aman untuk banyak parfum lokal harian.',
    priceMin: 100000,
    priceMax: 200000,
  },
  {
    value: '200-350',
    label: 'Rp200 ribu - Rp350 ribu',
    helper: 'Pilihan populer untuk EDP lokal dan ukuran menengah.',
    priceMin: 200000,
    priceMax: 350000,
  },
  {
    value: '350-500',
    label: 'Rp350 ribu - Rp500 ribu',
    helper: 'Untuk parfum dengan ukuran lebih besar atau lini khusus.',
    priceMin: 350000,
    priceMax: 500000,
  },
  {
    value: 'above-500',
    label: 'Di atas Rp500 ribu',
    helper: 'Buka pilihan yang lebih premium.',
    priceMin: 500000,
    priceMax: null,
  },
]

const intensityChoices: Array<Choice<IntensityPreference>> = [
  {
    value: 'no_preference',
    label: 'Tidak masalah',
    helper: 'Biarkan sistem menilai dari data lain.',
  },
  {
    value: 'soft',
    label: 'Soft',
    helper: 'Aroma dekat badan, cocok untuk situasi kalem.',
  },
  {
    value: 'medium',
    label: 'Medium',
    helper: 'Masih terasa, tapi tidak terlalu mendominasi.',
  },
  {
    value: 'strong',
    label: 'Strong',
    helper: 'Lebih tegas dan mudah tercium orang sekitar.',
  },
]

const genderChoices: Array<Choice<MarketedGenderPreference>> = [
  {
    value: 'no_preference',
    label: 'Tidak ada preferensi',
    helper: 'Fokus ke aroma dan pemakaian saja.',
  },
  {
    value: 'unisex',
    label: 'Unisex',
    helper: 'Terbuka untuk karakter yang tidak terlalu diarahkan ke gender tertentu.',
  },
  {
    value: 'pria',
    label: 'Arah pria',
    helper: 'Jika kamu mencari positioning yang lebih maskulin.',
  },
  {
    value: 'wanita',
    label: 'Arah wanita',
    helper: 'Jika kamu mencari positioning yang lebih feminin.',
  },
]

const blindBuyChoices: Array<Choice<BlindBuyComfort>> = [
  {
    value: 'safe',
    label: 'Aku ingin yang cenderung aman',
    helper: 'Prioritaskan karakter yang lebih mudah diterima untuk blind buy.',
  },
  {
    value: 'flexible',
    label: 'Sedikit unik tidak masalah',
    helper: 'Masih nyaman dengan aroma yang punya karakter khusus.',
  },
  {
    value: 'adventurous',
    label: 'Aku siap yang lebih berani',
    helper: 'Tidak masalah dengan profil yang lebih smoky, oud, atau intens.',
  },
]

const categoryHelpers: Record<string, string> = {
  fresh: 'Segar, ringan, sering terasa citrus, aquatic, atau mudah dipakai harian.',
  clean: 'Bersih, rapi, sabun, laundry, atau baru selesai mandi.',
  sweet: 'Manis umum, nyaman, dan tidak selalu dessert-like.',
  gourmand: 'Dessert-like, vanilla, caramel, kopi, atau creamy.',
  floral: 'Bunga, lembut, rapi, bisa terasa feminin atau clean tergantung komposisi.',
  woody: 'Kayu, cedar, sandalwood, kering, dan terasa lebih rapi.',
  earthy: 'Membumi, tanah, moss, akar, vetiver, atau patchouli.',
  warm: 'Hangat, nyaman, lembut menyelimuti.',
  amber: 'Amber, resinous, sedikit manis hangat, dan terasa glowing.',
  spicy: 'Rempah, saffron, pepper, atau bumbu hangat.',
  musky: 'Musk, skin-like, dekat, dan bersih di kulit.',
  powdery: 'Bedak, lembut kering, halus, dan rapi.',
  soft: 'Lembut, nyaman, kalem, dan cenderung low-risk.',
}

const occasionHelpers: Record<string, string> = {
  office: 'Untuk kerja, meeting, atau ruang bersama.',
  daily: 'Untuk pemakaian santai hampir setiap hari.',
  campus: 'Untuk kelas, kegiatan kampus, atau sekolah.',
  'casual-hangout': 'Untuk jalan santai, nongkrong, atau aktivitas ringan.',
  date: 'Untuk suasana lebih personal dan memorable.',
  formal: 'Untuk acara rapi, undangan, atau suasana profesional.',
  'evening-night': 'Untuk malam hari atau suasana yang lebih tegas.',
}

const buildPayload = (state: QuizState): RecommendationRequestPayload => {
  const budget = budgetChoices.find((choice) => choice.value === state.budget)

  return {
    occasion: state.occasion,
    aroma_preferences: state.aromaPreferences,
    price_min: budget?.priceMin ?? null,
    price_max: budget?.priceMax ?? null,
    intensity_preference: state.intensityPreference,
    avoided_tags: state.avoidedTags,
    blind_buy_comfort: state.blindBuyComfort || 'safe',
    marketed_gender_preference: state.marketedGenderPreference,
  }
}

const choiceClass = (isSelected: boolean) =>
  `quiz-choice ${isSelected ? 'quiz-choice--selected' : ''}`

type StoredQuizResults = {
  quizState: StoredQuizState
  recommendations: Recommendation[]
  viewMode?: 'results' | 'editing'
}

type StoredQuizState = Omit<QuizState, 'aromaPreferences'> & {
  aromaPreference?: string
  aromaPreferences?: string[]
}

const normalizeQuizState = (state?: StoredQuizState | null): QuizState => {
  if (!state) {
    return initialQuizState
  }

  const { aromaPreference, aromaPreferences: storedAromaPreferences, ...rest } = state
  const aromaPreferences = Array.isArray(storedAromaPreferences)
    ? storedAromaPreferences
    : aromaPreference
      ? [aromaPreference]
      : []

  return {
    ...initialQuizState,
    ...rest,
    aromaPreferences: Array.from(new Set(aromaPreferences)).slice(0, 3),
  }
}

const readStoredResults = (): StoredQuizResults | null => {
  try {
    const rawValue = window.sessionStorage.getItem(quizStorageKey)

    return rawValue ? (JSON.parse(rawValue) as StoredQuizResults) : null
  } catch {
    return null
  }
}

const writeStoredResults = (value: StoredQuizResults) => {
  try {
    window.sessionStorage.setItem(quizStorageKey, JSON.stringify(value))
  } catch {
    // The in-memory result state still works when sessionStorage is unavailable.
  }
}

const clearStoredResults = () => {
  try {
    window.sessionStorage.removeItem(quizStorageKey)
  } catch {
    // The quiz can still reset in memory when sessionStorage is unavailable.
  }
}

export function RecommendationQuizPage({
  locationSearch,
  onNavigate,
}: RecommendationQuizPageProps) {
  const shouldRestoreResults =
    new URLSearchParams(locationSearch).get('view') === 'results'
  const storedResults = readStoredResults()
  const restoredResults =
    storedResults &&
    (shouldRestoreResults || storedResults.viewMode === 'results')
      ? storedResults
      : null
  const cachedOccasions = api.getCachedOccasions()
  const cachedAromaCategories = api.getCachedAromaCategories()
  const cachedAromaTags = api.getCachedAromaTags()
  const hasCachedReferences = Boolean(
    cachedOccasions && cachedAromaCategories && cachedAromaTags,
  )
  const [quizState, setQuizState] = useState<QuizState>(
    normalizeQuizState(restoredResults?.quizState ?? storedResults?.quizState),
  )
  const [currentStep, setCurrentStep] = useState(0)
  const [occasions, setOccasions] = useState<Occasion[]>(
    cachedOccasions?.data ?? [],
  )
  const [aromaCategories, setAromaCategories] = useState<AromaCategory[]>(
    cachedAromaCategories?.data ?? [],
  )
  const [aromaTags, setAromaTags] = useState<AromaTag[]>(cachedAromaTags?.data ?? [])
  const [recommendations, setRecommendations] = useState<Recommendation[]>(
    restoredResults?.recommendations ?? [],
  )
  const [reasonModalSlug, setReasonModalSlug] = useState<string | null>(null)
  const [hasSubmitted, setHasSubmitted] = useState(Boolean(restoredResults))
  const [isLoadingReferences, setIsLoadingReferences] = useState(!hasCachedReferences)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(
    shouldRestoreResults && !restoredResults
      ? 'Hasil rekomendasi sebelumnya belum tersedia di sesi ini. Silakan isi quiz lagi.'
      : null,
  )

  useEffect(() => {
    let isMounted = true

    Promise.all([api.getOccasions(), api.getAromaCategories(), api.getAromaTags()])
      .then(([occasionResponse, categoryResponse, tagResponse]) => {
        if (!isMounted) {
          return
        }

        setOccasions(occasionResponse.data)
        setAromaCategories(categoryResponse.data)
        setAromaTags(tagResponse.data)
      })
      .catch(() => {
        if (isMounted) {
          setError('Data pilihan quiz belum bisa dimuat. Pastikan API Laravel aktif.')
        }
      })
      .finally(() => {
        if (isMounted) {
          setIsLoadingReferences(false)
        }
      })

    return () => {
      isMounted = false
    }
  }, [])

  const selectedBudget = useMemo(
    () => budgetChoices.find((choice) => choice.value === quizState.budget),
    [quizState.budget],
  )
  const modalRecommendation =
    recommendations.find(
      (recommendation) => recommendation.slug === reasonModalSlug,
    ) ?? null

  const validateStep = (step: number): string | null => {
    if (step === 0 && !quizState.occasion) {
      return 'Pilih dulu situasi pemakaian yang paling dekat.'
    }

    if (step === 1 && quizState.aromaPreferences.length === 0) {
      return 'Pilih dulu 1 sampai 3 aroma yang paling mendekati seleramu.'
    }

    if (step === 1 && quizState.aromaPreferences.length > 3) {
      return 'Maksimal pilih 3 kategori aroma agar rekomendasi tetap fokus.'
    }

    if (
      step === 2 &&
      selectedBudget &&
      selectedBudget.priceMin !== null &&
      selectedBudget.priceMax !== null &&
      selectedBudget.priceMax < selectedBudget.priceMin
    ) {
      return 'Rentang budget belum valid.'
    }

    if (step === 5 && !quizState.blindBuyComfort) {
      return 'Pilih dulu tingkat kenyamananmu untuk blind buy.'
    }

    return null
  }

  const goNext = () => {
    const validationMessage = validateStep(currentStep)

    if (validationMessage) {
      setError(validationMessage)
      return
    }

    setError(null)
    setCurrentStep((step) => Math.min(step + 1, totalSteps - 1))
  }

  const goBack = () => {
    setError(null)
    setCurrentStep((step) => Math.max(step - 1, 0))
  }

  const toggleAvoidedTag = (slug: string) => {
    setQuizState((state) => ({
      ...state,
      avoidedTags: state.avoidedTags.includes(slug)
        ? state.avoidedTags.filter((item) => item !== slug)
        : [...state.avoidedTags, slug],
    }))
  }

  const toggleAromaPreference = (slug: string) => {
    const isSelected = quizState.aromaPreferences.includes(slug)

    if (!isSelected && quizState.aromaPreferences.length >= 3) {
      setError('Maksimal pilih 3 kategori aroma agar rekomendasi tetap fokus.')

      return
    }

    setError(null)
    setQuizState((state) => {
      return {
        ...state,
        aromaPreferences: state.aromaPreferences.includes(slug)
          ? state.aromaPreferences.filter((item) => item !== slug)
          : [...state.aromaPreferences, slug],
      }
    })
  }

  const submitQuiz = () => {
    const validationMessage =
      validateStep(0) ?? validateStep(1) ?? validateStep(2) ?? validateStep(5)

    if (validationMessage) {
      setError(validationMessage)
      return
    }

    setError(null)
    setIsSubmitting(true)

    api
      .getRecommendations(buildPayload(quizState))
      .then((response) => {
        setRecommendations(response.recommendations)
        setReasonModalSlug(null)
        setHasSubmitted(true)
        writeStoredResults({
          quizState,
          recommendations: response.recommendations,
          viewMode: 'results',
        })
        window.scrollTo({ top: 0, behavior: 'smooth' })
      })
      .catch((requestError: unknown) => {
        const message =
          requestError instanceof Error
            ? requestError.message
            : 'Rekomendasi belum bisa dibuat. Coba lagi sebentar.'

        setError(message)
      })
      .finally(() => setIsSubmitting(false))
  }

  const editPreferences = () => {
    setHasSubmitted(false)
    setCurrentStep(0)
    setError(null)
    writeStoredResults({
      quizState,
      recommendations,
      viewMode: 'editing',
    })
    onNavigate('/quiz')
  }

  const resetQuiz = () => {
    clearStoredResults()
    setQuizState(initialQuizState)
    setRecommendations([])
    setReasonModalSlug(null)
    setHasSubmitted(false)
    setCurrentStep(0)
    setError(null)
    onNavigate('/quiz')
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  if (isLoadingReferences) {
    return (
      <main className="page page--compact">
        <section className="state-block" aria-live="polite">
          <span className="state-block__marker"></span>
          <h2>Sedang menyiapkan quiz</h2>
          <p>Nuanscent sedang mengambil pilihan aroma, occasion, dan tag dari API.</p>
        </section>
      </main>
    )
  }

  if (!hasSubmitted && error && occasions.length === 0 && aromaCategories.length === 0) {
    return (
      <main className="page page--compact">
        <ErrorBlock
          title="Quiz belum siap"
          message={error}
          actionLabel="Coba muat ulang"
          onAction={() => window.location.reload()}
        />
      </main>
    )
  }

  if (hasSubmitted) {
    return (
      <main className="page quiz-page">
        <section className="quiz-hero quiz-hero--results">
          <div>
            <p className="eyebrow">Hasil rekomendasi</p>
            <h1>Parfum yang paling mendekati preferensimu.</h1>
            <p>
              Urutan ini dihitung dari kecocokan aroma, occasion, budget, intensitas,
              dan kenyamanan blind buy. Ini panduan, bukan jaminan pasti cocok.
            </p>
          </div>
          <div className="quiz-result-actions">
            <button className="button button--secondary" type="button" onClick={editPreferences}>
              Ubah Preferensi
            </button>
            <button className="button button--ghost" type="button" onClick={resetQuiz}>
              Mulai ulang quiz
            </button>
          </div>
        </section>

        {recommendations.length > 0 ? (
          <>
            <section className="recommendation-list" aria-label="Daftar rekomendasi parfum">
              {recommendations.map((recommendation, index) => (
                <RecommendationCard
                  key={recommendation.slug}
                  recommendation={recommendation}
                  rank={index + 1}
                  detailReturnTo="/quiz?view=results"
                  isSelected={modalRecommendation?.slug === recommendation.slug}
                  onSelect={() => setReasonModalSlug(recommendation.slug)}
                  onNavigate={onNavigate}
                />
              ))}
            </section>

            {modalRecommendation ? (
              <RecommendationReasonModal
                recommendation={modalRecommendation}
                detailReturnTo="/quiz?view=results"
                onClose={() => setReasonModalSlug(null)}
                onNavigate={onNavigate}
              />
            ) : null}
          </>
        ) : (
          <EmptyBlock
            title="Belum ada rekomendasi yang cukup cocok"
            message="Coba longgarkan budget, pilih aroma yang lebih umum, atau kurangi aroma yang ingin dihindari."
            actionLabel="Ulangi Quiz"
            onAction={editPreferences}
          />
        )}
      </main>
    )
  }

  return (
    <main className="page quiz-page">
      <section className="quiz-hero">
        <div>
          <p className="eyebrow">Quiz rekomendasi parfum</p>
          <h1>Ceritakan kebutuhanmu, Nuanscent bantu pilihkan arah parfum.</h1>
          <p>
            Jawab beberapa pertanyaan sederhana. Kamu tidak perlu hafal istilah parfum;
            pilih yang terasa paling dekat dengan situasimu.
          </p>
        </div>
        <QuizProgress currentStep={currentStep + 1} totalSteps={totalSteps} />
      </section>

      <section className="quiz-panel">
        {error ? (
          <div className="quiz-alert" role="alert">
            {error}
          </div>
        ) : null}

        {currentStep === 0 ? (
          <QuizStep
            title="Parfum ini paling sering dipakai untuk situasi apa?"
            description="Occasion membantu sistem menilai apakah karakter parfum cocok untuk ruang kerja, harian, date, atau suasana yang lebih formal."
          >
            <div className="quiz-choice-grid">
              {occasions.map((occasion) => (
                <button
                  className={choiceClass(quizState.occasion === occasion.slug)}
                  type="button"
                  key={occasion.slug}
                  onClick={() => {
                    setError(null)
                    setQuizState((state) => ({ ...state, occasion: occasion.slug }))
                  }}
                >
                  <strong>{occasion.name}</strong>
                  <span>{occasionHelpers[occasion.slug] ?? occasion.description}</span>
                </button>
              ))}
            </div>
          </QuizStep>
        ) : null}

        {currentStep === 1 ? (
        <QuizStep
            title="Aroma seperti apa yang kamu cari?"
            description="Pilih 1 sampai 3 aroma yang paling mendekati seleramu."
          >
            <div className="quiz-choice-grid quiz-choice-grid--two">
              {aromaCategories.map((category) => (
                <button
                  className={choiceClass(quizState.aromaPreferences.includes(category.slug))}
                  type="button"
                  key={category.slug}
                  onClick={() => toggleAromaPreference(category.slug)}
                >
                  <strong>{category.name}</strong>
                  <span>{categoryHelpers[category.slug] ?? category.description}</span>
                </button>
              ))}
            </div>
          </QuizStep>
        ) : null}

        {currentStep === 2 ? (
          <QuizStep
            title="Berapa budget yang nyaman?"
            description="Budget dipakai sebagai rentang, jadi parfum masih bisa cocok jika harga produk bersinggungan dengan pilihanmu."
          >
            <div className="quiz-choice-grid quiz-choice-grid--two">
              {budgetChoices.map((choice) => (
                <button
                  className={choiceClass(quizState.budget === choice.value)}
                  type="button"
                  key={choice.value}
                  onClick={() => {
                    setError(null)
                    setQuizState((state) => ({ ...state, budget: choice.value }))
                  }}
                >
                  <strong>{choice.label}</strong>
                  <span>{choice.helper}</span>
                </button>
              ))}
            </div>
          </QuizStep>
        ) : null}

        {currentStep === 3 ? (
          <QuizStep
            title="Seberapa kuat aromanya yang kamu mau?"
            description="Kalau belum yakin, pilih tidak ada preferensi. Sistem tidak akan memaksa skor intensitas saat datanya belum tersedia."
          >
            <div className="quiz-choice-grid">
              {intensityChoices.map((choice) => (
                <button
                  className={choiceClass(quizState.intensityPreference === choice.value)}
                  type="button"
                  key={choice.value}
                  onClick={() =>
                    setQuizState((state) => ({
                      ...state,
                      intensityPreference: choice.value,
                    }))
                  }
                >
                  <strong>{choice.label}</strong>
                  <span>{choice.helper}</span>
                </button>
              ))}
            </div>

            <div className="quiz-subsection">
              <h3>Ada arah gender yang kamu prefer?</h3>
              <p>
                Ini opsional. Banyak parfum lokal tetap bisa dipakai lintas gender.
              </p>
              <div className="quiz-choice-grid">
                {genderChoices.map((choice) => (
                  <button
                    className={choiceClass(
                      quizState.marketedGenderPreference === choice.value,
                    )}
                    type="button"
                    key={choice.value}
                    onClick={() =>
                      setQuizState((state) => ({
                        ...state,
                        marketedGenderPreference: choice.value,
                      }))
                    }
                  >
                    <strong>{choice.label}</strong>
                    <span>{choice.helper}</span>
                  </button>
                ))}
              </div>
            </div>
          </QuizStep>
        ) : null}

        {currentStep === 4 ? (
          <QuizStep
            title="Ada aroma yang ingin kamu hindari?"
            description="Pilih sebanyak yang perlu. Ini tidak selalu menghapus parfum, tapi bisa menurunkan kecocokan jika parfum punya tag tersebut."
          >
            <div className="tag-choice-grid">
              {aromaTags.map((tag) => (
                <button
                  className={`tag-choice ${quizState.avoidedTags.includes(tag.slug) ? 'tag-choice--selected' : ''}`}
                  type="button"
                  key={tag.slug}
                  onClick={() => toggleAvoidedTag(tag.slug)}
                >
                  <strong>{tag.name}</strong>
                  <span>{tag.description ?? 'Hindari jika karakter ini biasanya kurang nyaman buatmu.'}</span>
                </button>
              ))}
            </div>
          </QuizStep>
        ) : null}

        {currentStep === 5 ? (
          <QuizStep
            title="Seberapa nyaman kamu untuk blind buy?"
            description="Blind-buy guidance bersifat hati-hati. Nuanscent tidak akan bilang parfum pasti aman, tapi akan menjelaskan risikonya."
          >
            <div className="quiz-choice-grid">
              {blindBuyChoices.map((choice) => (
                <button
                  className={choiceClass(quizState.blindBuyComfort === choice.value)}
                  type="button"
                  key={choice.value}
                  onClick={() => {
                    setError(null)
                    setQuizState((state) => ({
                      ...state,
                      blindBuyComfort: choice.value,
                    }))
                  }}
                >
                  <strong>{choice.label}</strong>
                  <span>{choice.helper}</span>
                </button>
              ))}
            </div>
          </QuizStep>
        ) : null}

        <div className="quiz-actions">
          <button
            className="button button--ghost"
            type="button"
            disabled={currentStep === 0 || isSubmitting}
            onClick={goBack}
          >
            Sebelumnya
          </button>
          {currentStep < totalSteps - 1 ? (
            <button className="button button--primary" type="button" onClick={goNext}>
              Berikutnya
            </button>
          ) : (
            <button
              className="button button--primary"
              type="button"
              disabled={isSubmitting}
              onClick={submitQuiz}
            >
              {isSubmitting ? 'Mencari rekomendasi...' : 'Lihat Rekomendasi'}
            </button>
          )}
        </div>
      </section>
    </main>
  )
}

function QuizStep({
  title,
  description,
  children,
}: {
  title: string
  description: string
  children: ReactNode
}) {
  return (
    <div className="quiz-step">
      <div className="quiz-step__header">
        <h2>{title}</h2>
        <p>{description}</p>
      </div>
      {children}
    </div>
  )
}
