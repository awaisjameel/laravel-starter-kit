export type CreateUserData = {
name: string;
email: string;
role: UserRole;
password: string;
};
export type PresenceMemberData = {
id: number;
name: string;
role: UserRole;
};
export type ProfileUpdateData = {
name: string;
email: string;
};
export type RegisterUserData = {
name: string;
email: string;
password: string;
};
export enum SharedRealtimeChannel { UserNotifications = 'users.{userId}.notifications' };
export enum SortDirection { Asc = 'asc', Desc = 'desc' };
export type UpdateUserData = {
name: string;
email: string;
role: UserRole;
password?: string;
};
export type UserChangedBroadcastData = {
action: UsersRealtimeAction;
actorUserId: number;
targetUserId: number;
user?: UserViewData;
occurredAt: string;
};
export type UserIndexData = {
page: number;
perPage: number;
search?: string;
sortBy: UserSortBy;
sortDirection: SortDirection;
};
export type UserManagementNotificationData = {
title: string;
description: string;
action: UsersRealtimeAction;
actorUserId: number;
actorName: string;
targetUserId?: number;
};
export enum UserRole { Admin = 'admin', User = 'user' };
export enum UserSortBy { Name = 'name', Email = 'email', Role = 'role', CreatedAt = 'created_at' };
export type UserViewData = {
id: number;
created_at: string;
updated_at: string;
name: string;
email: string;
role: UserRole;
email_verified_at?: string;
};
export type UsersIndexPageData = {
users: UsersPaginationData;
};
export type UsersListChangedBroadcastData = {
action: UsersRealtimeAction;
actorUserId: number;
targetUserId?: number;
occurredAt: string;
};
export type UsersPaginationData = {
data: Array<UserViewData>;
per_page: number;
current_page: number;
from?: number;
to?: number;
last_page: number;
total: number;
};
export enum UsersRealtimeAction { Create = 'create', Update = 'update', Delete = 'delete' };
export enum UsersRealtimeChannel { Index = 'users.index', Presence = 'users.index.presence', User = 'users.{userId}' };
export enum UsersRealtimeEvent { ListChanged = 'users.list.changed', UserChanged = 'users.user.changed' };
