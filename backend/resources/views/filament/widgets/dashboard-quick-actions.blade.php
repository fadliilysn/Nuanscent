<x-filament::section>
    <div class="nuanscent-dashboard-card">
        <div class="nuanscent-dashboard-card__header">
            <div>
                <p class="nuanscent-dashboard-card__eyebrow">Kurasi Nuanscent</p>
                <h2>Mulai dari data yang paling sering dikelola</h2>
                <p>
                    Tambahkan parfum, merek, atau panduan baru dari sini. Pastikan data parfum tetap berbasis sumber dan statusnya jelas sebelum diterbitkan.
                </p>
            </div>

            <span class="nuanscent-dashboard-card__badge">
                Admin katalog lokal
            </span>
        </div>

        <div class="nuanscent-dashboard-card__actions">
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
