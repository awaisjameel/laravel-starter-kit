import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

export function getYears() {
    const startYear = 1900
    const currentYear = new Date().getFullYear()
    return Array.from({ length: currentYear - startYear + 1 }, (_, i) => currentYear - i)
}

export function getMonths() {
    return Array.from({ length: 12 }, (_, i) => new Date(0, i).toLocaleString('default', { month: 'long' }))
}

type StringEnum = Record<string, string>

export function getEnumOptions<TEnum extends StringEnum>(enumType: TEnum): Array<{ value: TEnum[keyof TEnum]; label: string }> {
    return (Object.values(enumType) as Array<TEnum[keyof TEnum]>).map((value) => ({
        value,
        label: value
    }))
}
