export function getInitials(fullName?: string): string {
    if (!fullName) return ''

    const names = fullName.trim().split(' ')
    const firstName = names[0] ?? ''

    if (names.length === 0) return ''
    if (names.length === 1) return firstName.charAt(0).toUpperCase()

    const lastName = names[names.length - 1] ?? ''

    return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

export function useInitials() {
    return { getInitials }
}
