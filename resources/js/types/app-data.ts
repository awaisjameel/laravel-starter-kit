export enum Appearance {
    Light = 'light',
    Dark = 'dark'
}
export type ConfirmPasswordData = {
    password: string
}
export type CreateUserData = {
    name: string
    email: string
    role: UserRole
    password: string
}
export type CursorPaginatedDataCollection<TKey, TValue> = CursorPaginator<TKey, TValue>
export type CursorPaginator<TKey, TValue> = {
    data: TKey extends string ? Record<TKey, TValue> : TValue[]
    links: {
        url: string | null
        label: string
        active: boolean
    }[]
    meta: {
        path: string
        per_page: number
        next_cursor: string | null
        next_page_url: string | null
        prev_cursor: string | null
        prev_page_url: string | null
    }
}
export type CursorPaginatorInterface<TKey, TValue> = CursorPaginator<TKey, TValue>
export type ForgotPasswordPageData = {
    status?: string
}
export type LengthAwarePaginator<TKey, TValue> = {
    data: TKey extends string ? Record<TKey, TValue> : TValue[]
    links: {
        url: string | null
        label: string
        active: boolean
    }[]
    meta: {
        total: number
        current_page: number
        first_page_url: string
        from: number | null
        last_page: number
        last_page_url: string
        next_page_url: string | null
        path: string
        per_page: number
        prev_page_url: string | null
        to: number | null
    }
}
export type LengthAwarePaginatorInterface<TKey, TValue> = LengthAwarePaginator<TKey, TValue>
export type LoginData = {
    email: string
    password: string
    remember: boolean
}
export type LoginPageData = {
    canResetPassword: boolean
    status?: string
}
export type PaginatedDataCollection<TKey, TValue> = LengthAwarePaginator<TKey, TValue>
export type PasswordResetLinkData = {
    email: string
}
export type PasswordUpdateData = {
    currentPassword: string
    password: string
    passwordConfirmation: string
}
export type PresenceMemberData = {
    id: number
    name: string
    role: UserRole
}
export type ProfileDestroyData = {
    password: string
}
export type ProfilePageData = {
    mustVerifyEmail: boolean
    status?: string
}
export type ProfileUpdateData = {
    name: string
    email: string
}
export type RegisterUserData = {
    name: string
    email: string
    password: string
}
export type ResetPasswordData = {
    token: string
    email: string
    password: string
    passwordConfirmation: string
}
export type ResetPasswordPageData = {
    email: string
    token: string
}
export enum SharedRealtimeChannel {
    UserNotifications = 'users.{userId}.notifications'
}
export enum SortDirection {
    Asc = 'asc',
    Desc = 'desc'
}
export type UpdateUserData = {
    name: string
    email: string
    role: UserRole
    password?: string
}
export type UserChangedBroadcastData = {
    action: UsersRealtimeAction
    actorUserId: number
    targetUserId: number
    user?: UserViewData
    occurredAt: string
}
export type UserIndexData = {
    page: number
    perPage: number
    search?: string
    sortBy: UserSortBy
    sortDirection: SortDirection
}
export type UserManagementNotificationData = {
    title: string
    description: string
    action: UsersRealtimeAction
    actorUserId: number
    actorName: string
    targetUserId?: number
    occurredAt: string
}
export enum UserRole {
    Admin = 'admin',
    User = 'user'
}
export enum UserSortBy {
    Name = 'name',
    Email = 'email',
    Role = 'role',
    CreatedAt = 'created_at'
}
export type UserViewData = {
    id: number
    name: string
    email: string
    role: UserRole
    created_at: string
    updated_at: string
    email_verified_at?: string
}
export type UsersIndexPageData = {
    users: UsersPaginationData
}
export type UsersListChangedBroadcastData = {
    action: UsersRealtimeAction
    actorUserId: number
    targetUserId?: number
    occurredAt: string
}
export type UsersPaginationData = {
    data: UserViewData[]
    per_page: number
    current_page: number
    from?: number
    to?: number
    last_page: number
    total: number
}
export enum UsersRealtimeAction {
    Create = 'create',
    Update = 'update',
    Delete = 'delete'
}
export enum UsersRealtimeChannel {
    Index = 'users.index',
    Presence = 'users.index.presence',
    User = 'users.{userId}'
}
export enum UsersRealtimeEvent {
    ListChanged = 'users.list.changed',
    UserChanged = 'users.user.changed'
}
export type VerifyEmailPageData = {
    status?: string
}
