import { useEffect, useState } from 'react'
import { EmptyBlock } from './components/StateBlock'
import { PerfumeCatalogPage } from './pages/PerfumeCatalogPage'
import { PerfumeDetailPage } from './pages/PerfumeDetailPage'

type AppLocation = {
  pathname: string
  search: string
}

const readLocation = (): AppLocation => ({
  pathname: window.location.pathname,
  search: window.location.search,
})

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

  return (
    <div className="app-shell">
      <header className="site-header">
        <a
          className="brand-mark"
          href="/parfum"
          onClick={(event) => {
            event.preventDefault()
            navigate('/parfum')
          }}
        >
          <span className="brand-mark__stamp">N</span>
          <span>
            <strong>Nuanscent</strong>
            <small>Katalog parfum lokal</small>
          </span>
        </a>
      </header>

      {location.pathname === '/' || location.pathname === '/parfum' ? (
        <PerfumeCatalogPage
          key={location.search}
          locationSearch={location.search}
          onNavigate={navigate}
        />
      ) : detailMatch ? (
        <PerfumeDetailPage
          key={detailMatch[1]}
          slug={decodeURIComponent(detailMatch[1])}
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
