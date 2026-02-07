import { applyUrlDefaults, queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../wayfinder'
/**
 * @see \App\Http\Controllers\UserController::index
 * @see app/Http/Controllers/UserController.php:21
 * @route '/users'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get'
})

index.definition = {
    methods: ['get', 'head'],
    url: '/users'
} satisfies RouteDefinition<['get', 'head']>

/**
 * @see \App\Http\Controllers\UserController::index
 * @see app/Http/Controllers/UserController.php:21
 * @route '/users'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
 * @see \App\Http\Controllers\UserController::index
 * @see app/Http/Controllers/UserController.php:21
 * @route '/users'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get'
})
/**
 * @see \App\Http\Controllers\UserController::index
 * @see app/Http/Controllers/UserController.php:21
 * @route '/users'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head'
})

/**
 * @see \App\Http\Controllers\UserController::store
 * @see app/Http/Controllers/UserController.php:37
 * @route '/users'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post'
})

store.definition = {
    methods: ['post'],
    url: '/users'
} satisfies RouteDefinition<['post']>

/**
 * @see \App\Http\Controllers\UserController::store
 * @see app/Http/Controllers/UserController.php:37
 * @route '/users'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
 * @see \App\Http\Controllers\UserController::store
 * @see app/Http/Controllers/UserController.php:37
 * @route '/users'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post'
})

/**
 * @see \App\Http\Controllers\UserController::update
 * @see app/Http/Controllers/UserController.php:58
 * @route '/users/{user}'
 */
export const update = (
    args: { user: number | { id: number } } | [user: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put'
})

update.definition = {
    methods: ['put', 'patch'],
    url: '/users/{user}'
} satisfies RouteDefinition<['put', 'patch']>

/**
 * @see \App\Http\Controllers\UserController::update
 * @see app/Http/Controllers/UserController.php:58
 * @route '/users/{user}'
 */
update.url = (args: { user: number | { id: number } } | [user: number | { id: number }] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { user: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0]
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object' ? args.user.id : args.user
    }

    return update.definition.url.replace('{user}', parsedArgs.user.toString()).replace(/\/+$/, '') + queryParams(options)
}

/**
 * @see \App\Http\Controllers\UserController::update
 * @see app/Http/Controllers/UserController.php:58
 * @route '/users/{user}'
 */
update.put = (
    args: { user: number | { id: number } } | [user: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put'
})
/**
 * @see \App\Http\Controllers\UserController::update
 * @see app/Http/Controllers/UserController.php:58
 * @route '/users/{user}'
 */
update.patch = (
    args: { user: number | { id: number } } | [user: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch'
})

/**
 * @see \App\Http\Controllers\UserController::destroy
 * @see app/Http/Controllers/UserController.php:110
 * @route '/users/{user}'
 */
export const destroy = (
    args: { user: number | { id: number } } | [user: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete'
})

destroy.definition = {
    methods: ['delete'],
    url: '/users/{user}'
} satisfies RouteDefinition<['delete']>

/**
 * @see \App\Http\Controllers\UserController::destroy
 * @see app/Http/Controllers/UserController.php:110
 * @route '/users/{user}'
 */
destroy.url = (args: { user: number | { id: number } } | [user: number | { id: number }] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { user: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0]
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object' ? args.user.id : args.user
    }

    return destroy.definition.url.replace('{user}', parsedArgs.user.toString()).replace(/\/+$/, '') + queryParams(options)
}

/**
 * @see \App\Http\Controllers\UserController::destroy
 * @see app/Http/Controllers/UserController.php:110
 * @route '/users/{user}'
 */
destroy.delete = (
    args: { user: number | { id: number } } | [user: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete'
})
const UserController = { index, store, update, destroy }

export default UserController
