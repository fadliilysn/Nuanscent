<x-filament::section>
    <div style="display:grid;gap:1.25rem;">
        <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem;">
            <div style="max-width:42rem;">
                <p style="margin:0 0 .4rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#b98218;">
                    Kurasi Nuanscent
                </p>
                <h2 style="margin:0;color:#1f1a14;font-size:1.35rem;font-weight:800;line-height:1.2;">
                    Mulai dari data yang paling sering dikelola
                </h2>
                <p style="margin:.55rem 0 0;color:#786452;font-size:.95rem;line-height:1.55;">
                    Tambahkan parfum, merek, atau panduan baru dari sini. Pastikan data parfum tetap berbasis sumber dan statusnya jelas sebelum diterbitkan.
                </p>
            </div>

            <span style="display:inline-flex;align-items:center;border-radius:999px;background:#fff4d6;color:#725016;padding:.45rem .75rem;font-size:.8rem;font-weight:700;">
                Admin katalog lokal
            </span>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
            <x-filament::button
                tag="a"
                :href="\App\Filament\Resources\Perfumes\PerfumeResource::getUrl('create')"
                icon="heroicon-o-sparkles"
            >
                Tambah Parfum
            </x-filament::button>

            <x-filament::button
                tag="a"
                color="gray"
                :href="\App\Filament\Resources\Brands\BrandResource::getUrl('create')"
                icon="heroicon-o-building-storefront"
                outlined
            >
                Tambah Merek
            </x-filament::button>

            <x-filament::button
                tag="a"
                color="gray"
                :href="\App\Filament\Resources\Guides\GuideResource::getUrl('create')"
                icon="heroicon-o-book-open"
                outlined
            >
                Tambah Panduan
            </x-filament::button>
        </div>
    </div>
</x-filament::section>
