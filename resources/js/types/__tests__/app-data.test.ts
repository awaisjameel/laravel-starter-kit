import { expectTypeOf, it } from 'vitest'
import type { UserChangedBroadcastData, UserIndexData, UserManagementNotificationData, UsersPaginationData, UserViewData } from '../app-data'

it('preserves explicit nulls in backend response contracts', () => {
    expectTypeOf<UserViewData['email_verified_at']>().toEqualTypeOf<string | null>()
    expectTypeOf<UserChangedBroadcastData['user']>().toEqualTypeOf<UserViewData | null>()
    expectTypeOf<UserManagementNotificationData['targetUserId']>().toEqualTypeOf<number | null>()
    expectTypeOf<UsersPaginationData['from']>().toEqualTypeOf<number | null>()
    expectTypeOf<UsersPaginationData['to']>().toEqualTypeOf<number | null>()
    expectTypeOf<UserIndexData['search']>().toEqualTypeOf<string | null>()
})
