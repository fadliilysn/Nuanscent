import { useEffect, useState, type MouseEvent } from "react";
import { PerfumeCard } from "../components/PerfumeCard";
import { api } from "../lib/api";
import type { AromaCategory, Brand, Perfume } from "../types/api";

type HomePageProps = {
  onNavigate: (to: string) => void;
};

type HomeData = {
  brands: Brand[];
  aromaCategories: AromaCategory[];
  perfumes: Perfume[];
};

const initialHomeData: HomeData = {
  brands: [],
  aromaCategories: [],
  perfumes: [],
};

const HOME_BRAND_LIMIT = 8;

const readCachedHomeData = (): HomeData => ({
  brands: api.getCachedBrands()?.data ?? [],
  aromaCategories: api.getCachedAromaCategories()?.data ?? [],
  perfumes: api.getCachedPerfumes({ per_page: "6" })?.data ?? [],
});

const hasCompleteHomeCache = (data: HomeData) => data.brands.length > 0 && data.aromaCategories.length > 0 && data.perfumes.length > 0;

const aromaExplorerContent: Record<string, { icon: string; description: string; order: number }> = {
  amber: {
    icon: "\u2728",
    description: "Hangat, resinous, dan sensual.",
    order: 1,
  },
  clean: {
    icon: "\ud83e\udee7",
    description: "Bersih, ringan, dan fresh.",
    order: 2,
  },
  earthy: {
    icon: "\ud83c\udf3f",
    description: "Natural, hijau, dan membumi.",
    order: 3,
  },
  floral: {
    icon: "\ud83c\udf38",
    description: "Lembut, romantis, dan elegan.",
    order: 4,
  },
  fresh: {
    icon: "\ud83c\udf43",
    description: "Segar, ringan, dan energik.",
    order: 5,
  },
  gourmand: {
    icon: "\ud83c\udf6e",
    description: "Manis, creamy, dan edible.",
    order: 6,
  },
  musky: {
    icon: "\u2601\ufe0f",
    description: "Halus, skin-like, dan sensual.",
    order: 7,
  },
  powdery: {
    icon: "\ud83d\udd4a\ufe0f",
    description: "Lembut, airy, dan comforting.",
    order: 8,
  },
  soft: {
    icon: "\ud83e\udd0d",
    description: "Smooth, cozy, dan easy to wear.",
    order: 9,
  },
  spicy: {
    icon: "\ud83c\udf36\ufe0f",
    description: "Hangat, tajam, dan berkarakter.",
    order: 10,
  },
  sweet: {
    icon: "\ud83c\udf6f",
    description: "Manis, playful, dan cozy.",
    order: 11,
  },
  warm: {
    icon: "\ud83d\udd06",
    description: "Hangat, comforting, dan rich.",
    order: 12,
  },
  woody: {
    icon: "\ud83e\udeb5",
    description: "Elegan, hangat, dan mature.",
    order: 13,
  },
};

const preventAndNavigate = (event: MouseEvent<HTMLAnchorElement>, to: string, onNavigate: (to: string) => void) => {
  event.preventDefault();
  onNavigate(to);
};

export function HomePage({ onNavigate }: HomePageProps) {
  const cachedHomeData = readCachedHomeData();
  const [homeData, setHomeData] = useState<HomeData>(hasCompleteHomeCache(cachedHomeData) ? cachedHomeData : initialHomeData);
  const [isLoading, setIsLoading] = useState(!hasCompleteHomeCache(cachedHomeData));
  const [hasLoadError, setHasLoadError] = useState(false);
  const featuredBrands = homeData.brands.slice(0, HOME_BRAND_LIMIT);

  useEffect(() => {
    let isMounted = true;
    const cachedData = readCachedHomeData();

    Promise.allSettled([api.getBrands(), api.getAromaCategories(), api.getPerfumes({ per_page: "4" })])
      .then(([brandResult, categoryResult, perfumeResult]) => {
        if (!isMounted) {
          return;
        }

        setHomeData({
          brands: brandResult.status === "fulfilled" ? brandResult.value.data : cachedData.brands,
          aromaCategories: categoryResult.status === "fulfilled" ? categoryResult.value.data : cachedData.aromaCategories,
          perfumes: perfumeResult.status === "fulfilled" ? perfumeResult.value.data : cachedData.perfumes,
        });

        setHasLoadError(brandResult.status === "rejected" || categoryResult.status === "rejected" || perfumeResult.status === "rejected");
      })
      .finally(() => {
        if (isMounted) {
          setIsLoading(false);
        }
      });

    return () => {
      isMounted = false;
    };
  }, []);

  return (
    <main className="page home-page">
      <section className="home-hero">
        <div className="home-hero__content">
          <div className="home-hero__brand">
            <img src="/images/logo-nuanscent.png" alt="" aria-hidden="true" decoding="async" />
            <div>
              <strong>Nuanscent</strong>
              <span>Katalog parfum lokal</span>
            </div>
          </div>
          <h1>Temukan parfum lokal tanpa harus menebak-nebak</h1>
          <p>Nuanscent membantu kamu memilih parfum lokal Indonesia lewat quiz sederhana, katalog terfilter, dan penjelasan yang ramah untuk pemula.</p>
          <div className="home-hero__actions">
            <a className="button button--primary" href="/quiz" onClick={(event) => preventAndNavigate(event, "/quiz", onNavigate)}>
              Mulai quiz
            </a>
            <a className="button button--secondary" href="/parfum" onClick={(event) => preventAndNavigate(event, "/parfum", onNavigate)}>
              Jelajahi katalog
            </a>
          </div>
        </div>
      </section>

      <section className="home-section">
        <SectionHeader eyebrow="Mulai dari mana?" title="Pilih jalur yang paling sesuai dengan cara kamu mencari parfum" />
        <div className="home-choice-grid">
          <a className="home-choice-card" href="/quiz" onClick={(event) => preventAndNavigate(event, "/quiz", onNavigate)}>
            <span>01</span>
            <div>
              <h3>Masih bingung aromanya?</h3>
              <p>Jawab quiz singkat agar Nuanscent memberi kandidat yang lebih relevan.</p>
            </div>
          </a>
          <a className="home-choice-card" href="/parfum" onClick={(event) => preventAndNavigate(event, "/parfum", onNavigate)}>
            <span>02</span>
            <div>
              <h3>Mau lihat semua pilihan?</h3>
              <p>Masuk ke katalog dan filter parfum berdasarkan brand, aroma, dan harga.</p>
            </div>
          </a>
          <a className="home-choice-card" href="/guides" onClick={(event) => preventAndNavigate(event, "/guides", onNavigate)}>
            <span>03</span>
            <div>
              <h3>Mau belajar istilah parfum?</h3>
              <p>Baca panduan aroma, notes, dan tips blind-buy untuk pemula.</p>
            </div>
          </a>
        </div>
      </section>

      <section className="home-section">
        <SectionHeader eyebrow="Brand lokal" title="Mulai dari brand yang sudah ada di katalog" actionLabel="Lihat semua brands" actionHref="/brands" onNavigate={onNavigate} />
        {isLoading ? (
          <InlineState message="Sedang memuat brand lokal." />
        ) : featuredBrands.length > 0 ? (
          <div className="home-brand-grid">
            {featuredBrands.map((brand) => {
              const brandPath = `/brands/${encodeURIComponent(brand.slug)}`;

              return (
                <a className="home-brand-card" href={brandPath} key={brand.slug} onClick={(event) => preventAndNavigate(event, brandPath, onNavigate)}>
                  <BrandLogo brand={brand} />
                  <div>
                    <h3>{brand.name}</h3>
                  </div>
                </a>
              );
            })}
          </div>
        ) : (
          <InlineState message="Brand belum bisa ditampilkan saat ini. Coba lagi sebentar." />
        )}
      </section>

      <section className="home-section">
        <SectionHeader eyebrow="Eksplorasi aroma" title="Cari dari keluarga aroma yang paling dekat dengan seleramu" />
        <p className="home-section__intro">Temukan karakter aroma yang sesuai dengan mood, aktivitas, dan gaya parfum yang kamu sukai.</p>
        {isLoading ? (
          <InlineState message="Sedang memuat kategori aroma." />
        ) : homeData.aromaCategories.length > 0 ? (
          <div className="home-aroma-grid">
            {[...homeData.aromaCategories]
              .sort((first, second) => (aromaExplorerContent[first.slug]?.order ?? Number.MAX_SAFE_INTEGER) - (aromaExplorerContent[second.slug]?.order ?? Number.MAX_SAFE_INTEGER))
              .map((category) => {
                const categoryPath = `/parfum?aroma_category=${encodeURIComponent(category.slug)}`;
                const content = aromaExplorerContent[category.slug];

                return (
                  <a aria-label={`Lihat parfum dengan aroma ${category.name}`} className="home-aroma-card" href={categoryPath} key={category.slug} onClick={(event) => preventAndNavigate(event, categoryPath, onNavigate)}>
                    <span className="home-aroma-card__icon" aria-hidden="true">
                      {content?.icon ?? "\u2726"}
                    </span>
                    <h3>{category.name}</h3>
                    <p>{content?.description ?? category.description}</p>
                  </a>
                );
              })}
          </div>
        ) : (
          <InlineState message="Kategori aroma belum bisa ditampilkan saat ini. Coba lagi sebentar." />
        )}
      </section>

      <section className="home-section">
        <SectionHeader eyebrow="Preview katalog" title="Beberapa parfum lokal untuk mulai dijelajahi" actionLabel="Buka katalog lengkap" actionHref="/parfum" onNavigate={onNavigate} />
        {hasLoadError ? <InlineState message="Sebagian isi halaman belum bisa dimuat. Kamu tetap bisa membuka katalog lewat tombol di atas." /> : null}
        {isLoading ? (
          <InlineState message="Sedang memuat preview parfum." />
        ) : homeData.perfumes.length > 0 ? (
          <div className="perfume-grid home-perfume-grid">
            {homeData.perfumes.map((perfume) => (
              <PerfumeCard key={perfume.slug} perfume={perfume} onNavigate={onNavigate} />
            ))}
          </div>
        ) : (
          <InlineState message="Preview parfum belum tersedia. Silakan buka katalog untuk mencoba lagi." />
        )}
      </section>

      <HomeFooter />
    </main>
  );
}

function SectionHeader({ eyebrow, title, actionLabel, actionHref, onNavigate }: { eyebrow: string; title: string; actionLabel?: string; actionHref?: string; onNavigate?: (to: string) => void }) {
  return (
    <div className="home-section__header">
      <div>
        <p className="eyebrow">{eyebrow}</p>
        <h2>{title}</h2>
      </div>
      {actionLabel && actionHref && onNavigate ? (
        <a className="button button--ghost home-section-cta" href={actionHref} onClick={(event) => preventAndNavigate(event, actionHref, onNavigate)}>
          {actionLabel}
        </a>
      ) : null}
    </div>
  );
}

function HomeFooter() {
  return (
    <footer className="home-footer">
      <div className="home-footer__inner">
        <div className="home-footer__brand">
          <div>
            <strong>Nuanscent</strong>
            <span>Katalog parfum lokal</span>
          </div>
          <p>Temukan parfum lokal berdasarkan aroma, brand, dan preferensi yang paling dekat dengan seleramu.</p>
        </div>
        <div className="home-footer__meta">
          <p>Nuanscent dibuat sebagai katalog dan rekomendasi parfum lokal untuk eksplorasi. Informasi produk dapat berubah mengikuti sumber brand atau toko resmi.</p>
          <small>{"\u00a9"} 2026 Nuanscent. All rights reserved.</small>
        </div>
      </div>
    </footer>
  );
}

function InlineState({ message }: { message: string }) {
  return <p className="home-inline-state">{message}</p>;
}

function BrandLogo({ brand }: { brand: Brand }) {
  const [hasImageError, setHasImageError] = useState(false);

  return (
    <span className="brand-logo" aria-hidden="true">
      {brand.logo_url && !hasImageError ? (
        <img
          src={brand.logo_url}
          alt=""
          loading="lazy"
          decoding="async"
          onError={() => setHasImageError(true)}
        />
      ) : (
        <span>{brand.name.slice(0, 1).toUpperCase()}</span>
      )}
    </span>
  );
}
