/** Display helpers for stock quantities and documents. Pure functions, no state. */

export const formatQty = (value: string | number): string =>
  Number(value).toLocaleString(undefined, { maximumFractionDigits: 3 })

/** "+5" / "-2.5" for ledger rows. */
export const formatSignedQty = (value: string | number): string => {
  const n = Number(value)
  return `${n > 0 ? '+' : ''}${formatQty(n)}`
}

export const formatDateTime = (iso: string): string => new Date(iso).toLocaleString()

export const todayISO = (): string => new Date().toISOString().slice(0, 10)

interface NamedVariant {
  name: string
  product?: { name: string } | null
}

/** "Mineral Water 500ml - Red / M" (the "Default" variant name of single products is hidden). */
export function variantLabel(variant: NamedVariant): string {
  const product = variant.product?.name ?? ''
  const suffix = variant.name && variant.name !== 'Default' ? ` - ${variant.name}` : ''
  return `${product}${suffix}`.trim() || variant.name
}
