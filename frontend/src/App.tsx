import { useEffect, useState } from 'react'
import { EmptyBlock } from './components/StateBlock'
import { BrandDetailPage } from './pages/BrandDetailPage'
import { BrandListPage } from './pages/BrandListPage'
import { GuideDetailPage } from './pages/GuideDetailPage'
import { GuideListPage } from './pages/GuideListPage'
import { HomePage } from './pages/HomePage'
import { PerfumeCatalogPage } from './pages/PerfumeCatalogPage'
import { PerfumeDetailPage } from './pages/PerfumeDetailPage'
import { RecommendationQuizPage } from './pages/RecommendationQuizPage'

type AppLocation = {
  pathname: string
  search: string
}

const readLocation = (): AppLocation => ({
  pathname: window.location.pathname,
  search: window.location.search,
})

const safeReturnTo = (locationSearch: string) => {
  const returnTo = new URLSearchParams(locationSearch).get('returnTo')

  if (!returnTo) {
    return null
  }

  if (
    returnTo === '/parfum' ||
    returnTo.startsWith('/parfum?') ||
    returnTo === '/brands' ||
    returnTo.startsWith('/brands/') ||
    returnTo === '/merek' ||
    returnTo.startsWith('/merek/') ||
    returnTo === '/quiz?view=results'
  ) {
    return returnTo
  }

  return null
}

function App() {
  const [location, setLocation] = useState<AppLocation>(readLocation)

  const navigate = (to: string) => {
    window.history.pushState({}, '', to)
    setLocation(readLocation())
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  useEffect(() => {
    const handlePopState = () => setLocation(readLocation())

    window.addEventListener('popstate', handlePopState)

    return () => window.removeEventListener('popstate', handlePopState)
  }, [])

  const detailMatch = location.pathname.match(/^\/parfum\/([^/]+)$/)
  const brandDetailMatch = location.pathname.match(/^\/brands\/([^/]+)$/)
  const legacyBrandDetailMatch = location.pathname.match(/^\/merek\/([^/]+)$/)
  const brandDetailSlug = brandDetailMatch?.[1] ?? legacyBrandDetailMatch?.[1]
  const guideDetailMatch = location.pathname.match(/^\/guides\/([^/]+)$/)

  return (
    <div className="app-shell">
      <header className="site-header">
        <a
          className="brand-mark"
          href="/"
          onClick={(event) => {
            event.preventDefault()
            navigate('/')
          }}
        >
          <span className="brand-mark__stamp">N</span>
          <span>
            <strong>Nuanscent</strong>
            <small>Katalog parfum lokal</small>
          </span>
        </a>
        <nav className="site-nav" aria-label="Navigasi utama">
          <a
            href="/quiz"
            onClick={(event) => {
              event.preventDefault()
              navigate('/quiz')
            }}
          >
            Quiz
          </a>
          <a
            href="/brands"
            onClick={(event) => {
              event.preventDefault()
              navigate('/brands')
            }}
          >
            Merek
          </a>
          <a
            href="/parfum"
            onClick={(event) => {
              event.preventDefault()
              navigate('/parfum')
            }}
          >
            Katalog
          </a>
        </nav>
      </header>

      {location.pathname === '/' ? (
        <HomePage onNavigate={navigate} />
      ) : location.pathname === '/guides' ? (
        <GuideListPage onNavigate={navigate} />
      ) : guideDetailMatch ? (
        <GuideDetailPage
          key={guideDetailMatch[1]}
          slug={decodeURIComponent(guideDetailMatch[1])}
          onNavigate={navigate}
        />
      ) : location.pathname === '/parfum' ? (
        <PerfumeCatalogPage
          key={location.search}
          locationSearch={location.search}
          onNavigate={navigate}
        />
      ) : location.pathname === '/brands' || location.pathname === '/merek' ? (
        <BrandListPage onNavigate={navigate} />
      ) : brandDetailSlug ? (
        <BrandDetailPage
          key={brandDetailSlug}
          slug={decodeURIComponent(brandDetailSlug)}
          onNavigate={navigate}
        />
      ) : location.pathname === '/quiz' ? (
        <RecommendationQuizPage
          locationSearch={location.search}
          onNavigate={navigate}
        />
      ) : detailMatch ? (
        <PerfumeDetailPage
          key={detailMatch[1]}
          slug={decodeURIComponent(detailMatch[1])}
          returnTo={safeReturnTo(location.search)}
          onNavigate={navigate}
        />
      ) : (
        <main className="page page--compact">
          <EmptyBlock
            title="Halaman tidak ditemukan"
            message="Rute ini belum tersedia di Nuanscent."
            actionLabel="Lihat katalog parfum"
            onAction={() => navigate('/parfum')}
          />
        </main>
      )}
    </div>
  )
}

export default App
