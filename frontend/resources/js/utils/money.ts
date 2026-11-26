/**
 * All money crosses the API as integers in minor units (paisa/cents).
 * Convert only at the UI boundary.
 */
const FRACTION_DIGITS = 2

export const toMinor = (major: number | string): number =>
  Math.round(Number(major) * 10 ** FRACTION_DIGITS)

export const toMajor = (minor: number): number => minor / 10 ** FRACTION_DIGITS

export const formatMoney = (minor: number, currency = 'BDT', locale = 'en-BD'): string =>
  new Intl.NumberFormat(locale, { style: 'currency', currency }).format(toMajor(minor))
