export type CreateUserData = {
name: string;
email: string;
role: UserRole;
password: string;
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
export enum SortDirection { Asc = 'asc', Desc = 'desc' };
export type UpdateUserData = {
name: string;
email: string;
role: UserRole;
password?: string;
};
export type UserIndexData = {
page: number;
perPage: number;
search?: string;
sortBy: UserSortBy;
sortDirection: SortDirection;
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
export type UsersPaginationData = {
data: Array<UserViewData>;
per_page: number;
current_page: number;
from?: number;
to?: number;
last_page: number;
total: number;
};
