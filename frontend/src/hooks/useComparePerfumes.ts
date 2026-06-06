import { useEffect, useState } from 'react'
import type { Perfume } from '../types/api'

const compareStorageKey = 'nuanscent.compare-perfumes'
const maxComparedPerfumes = 3

export type ComparePerfumeItem = {
  id: number
  slug: string
  name: string
  imageUrl: string | null
  brandName: string
  aromaCategoryName: string | null
  priceMin: number | null
  priceMax: number | null
  shortDescription: string | null
}

type ToggleResult = 'added' | 'removed' | 'limit'

const isNullableString = (value: unknown) =>
  value === null || typeof value === 'string'

const isNullableNumber = (value: unknown) =>
  value === null || typeof value === 'number'

const isComparePerfumeItem = (value: unknown): value is ComparePerfumeItem => {
  if (!value || typeof value !== 'object') {
    return false
  }

  const item = value as Partial<ComparePerfumeItem>

  return (
    typeof item.id === 'number' &&
    typeof item.slug === 'string' &&
    item.slug.length > 0 &&
    typeof item.name === 'string' &&
    item.name.length > 0 &&
    typeof item.brandName === 'string' &&
    isNullableString(item.imageUrl) &&
    isNullableString(item.aromaCategoryName) &&
    isNullableNumber(item.priceMin) &&
    isNullableNumber(item.priceMax) &&
    isNullableString(item.shortDescription)
  )
}

const readStoredItems = () => {
  try {
    const storedValue = window.localStorage.getItem(compareStorageKey)

    if (!storedValue) {
      return []
    }

    const parsedValue = JSON.parse(storedValue) as unknown

    if (!Array.isArray(parsedValue)) {
      return []
    }

    return parsedValue.filter(isComparePerfumeItem).slice(0, maxComparedPerfumes)
  } catch {
    return []
  }
}

const toCompareItem = (perfume: Perfume): ComparePerfumeItem => ({
  id: perfume.id,
  slug: perfume.slug,
  name: perfume.name,
  imageUrl: perfume.image_url,
  brandName: perfume.brand?.name ?? 'Brand belum tersedia',
  aromaCategoryName: perfume.main_aroma_category?.name ?? null,
  priceMin: perfume.price_min,
  priceMax: perfume.price_max,
  shortDescription: perfume.short_description,
})

export function useComparePerfumes() {
  const [items, setItems] = useState<ComparePerfumeItem[]>(readStoredItems)

  useEffect(() => {
    try {
      window.localStorage.setItem(compareStorageKey, JSON.stringify(items))
    } catch {
      // Comparison still works in memory when browser storage is unavailable.
    }
  }, [items])

  const togglePerfume = (perfume: Perfume): ToggleResult => {
    const existingItem = items.find((item) => item.slug === perfume.slug)

    if (existingItem) {
      setItems((currentItems) =>
        currentItems.filter((item) => item.slug !== perfume.slug),
      )
      return 'removed'
    }

    if (items.length >= maxComparedPerfumes) {
      return 'limit'
    }

    setItems((currentItems) => [...currentItems, toCompareItem(perfume)])
    return 'added'
  }

  const removePerfume = (slug: string) => {
    setItems((currentItems) => currentItems.filter((item) => item.slug !== slug))
  }

  const clearPerfumes = () => setItems([])

  return {
    items,
    maxItems: maxComparedPerfumes,
    isAtLimit: items.length >= maxComparedPerfumes,
    isSelected: (slug: string) => items.some((item) => item.slug === slug),
    togglePerfume,
    removePerfume,
    clearPerfumes,
  }
}
