export enum UserRole {
    Admin = 'admin',
    User = 'user'
}
export type UserViewData = {
    id: number
    created_at: string
    updated_at: string
    name: string
    email: string
    role: UserRole
    email_verified_at?: string
}
