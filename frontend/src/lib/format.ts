export const formatPriceRange = (
  priceMin: number | null,
  priceMax: number | null,
) => {
  if (priceMin === null && priceMax === null) {
    return 'Harga belum tersedia'
  }

  const format = (value: number) =>
    new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    }).format(value)

  if (priceMin !== null && priceMax !== null) {
    return priceMin === priceMax
      ? format(priceMin)
      : `${format(priceMin)} - ${format(priceMax)}`
  }

  return priceMin !== null ? `Mulai ${format(priceMin)}` : `Hingga ${format(priceMax!)}`
}

export const formatVolume = (volumeMl: number | null) =>
  volumeMl === null ? 'Volume belum tersedia' : `${volumeMl} ml`

export const formatOptional = (value: string | number | null | undefined) =>
  value === null || value === undefined || value === '' ? 'Belum tersedia' : value
