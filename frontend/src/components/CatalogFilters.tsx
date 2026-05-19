import type { FormEvent } from 'react'
import type { AromaCategory, AromaTag, Brand, CatalogFilters, Occasion } from '../types/api'

type CatalogFiltersProps = {
  filters: CatalogFilters
  brands: Brand[]
  aromaCategories: AromaCategory[]
  aromaTags: AromaTag[]
  occasions: Occasion[]
  onChange: (filters: CatalogFilters) => void
  onSubmit: () => void
  onReset: () => void
}

export function CatalogFilters({
  filters,
  brands,
  aromaCategories,
  aromaTags,
  occasions,
  onChange,
  onSubmit,
  onReset,
}: CatalogFiltersProps) {
  const updateFilter = (key: keyof CatalogFilters, value: string) => {
    onChange({ ...filters, [key]: value })
  }

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    onSubmit()
  }

  return (
    <details className="filter-disclosure" open>
      <summary>Filter katalog</summary>

      <form className="filter-panel" onSubmit={handleSubmit}>
        <div className="filter-panel__header">
          <div>
            <p className="eyebrow">Filter katalog</p>
            <h2>Persempit pilihan</h2>
          </div>
          <button className="button button--ghost" type="button" onClick={onReset}>
            Reset
          </button>
        </div>

        <label className="field field--wide">
          <span>Cari nama parfum</span>
          <input
            type="search"
            value={filters.search ?? ''}
            placeholder="Contoh: Farhampton, dll"
            onChange={(event) => updateFilter('search', event.target.value)}
          />
        </label>

        <label className="field">
          <span>Brand</span>
          <select
            value={filters.brand ?? ''}
            onChange={(event) => updateFilter('brand', event.target.value)}
          >
            <option value="">Semua brand</option>
            {brands.map((brand) => (
              <option key={brand.slug} value={brand.slug}>
                {brand.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Kategori aroma</span>
          <select
            value={filters.aroma_category ?? ''}
            onChange={(event) => updateFilter('aroma_category', event.target.value)}
          >
            <option value="">Semua kategori</option>
            {aromaCategories.map((category) => (
              <option key={category.slug} value={category.slug}>
                {category.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Tag aroma</span>
          <select
            value={filters.aroma_tag ?? ''}
            onChange={(event) => updateFilter('aroma_tag', event.target.value)}
          >
            <option value="">Semua tag</option>
            {aromaTags.map((tag) => (
              <option key={tag.slug} value={tag.slug}>
                {tag.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Occasion</span>
          <select
            value={filters.occasion ?? ''}
            onChange={(event) => updateFilter('occasion', event.target.value)}
          >
            <option value="">Semua occasion</option>
            {occasions.map((occasion) => (
              <option key={occasion.slug} value={occasion.slug}>
                {occasion.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Harga minimum</span>
          <input
            min="0"
            type="number"
            value={filters.price_min ?? ''}
            placeholder="100000"
            onChange={(event) => updateFilter('price_min', event.target.value)}
          />
        </label>

        <label className="field">
          <span>Harga maksimum</span>
          <input
            min="0"
            type="number"
            value={filters.price_max ?? ''}
            placeholder="300000"
            onChange={(event) => updateFilter('price_max', event.target.value)}
          />
        </label>

        <button className="button button--primary filter-panel__submit" type="submit">
          Terapkan filter
        </button>
      </form>
    </details>
  )
}
