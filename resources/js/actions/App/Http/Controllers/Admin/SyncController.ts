import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SyncController::dispatchSync
* @see [unknown]:0
* @route '/admin/sync'
*/
export const dispatchSync = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dispatchSync.url(options),
    method: 'post',
})

dispatchSync.definition = {
    methods: ["post"],
    url: '/admin/sync',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatchSync
* @see [unknown]:0
* @route '/admin/sync'
*/
dispatchSync.url = (options?: RouteQueryOptions) => {
    return dispatchSync.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatchSync
* @see [unknown]:0
* @route '/admin/sync'
*/
dispatchSync.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dispatchSync.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatchSync
* @see [unknown]:0
* @route '/admin/sync'
*/
const dispatchSyncForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: dispatchSync.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatchSync
* @see [unknown]:0
* @route '/admin/sync'
*/
dispatchSyncForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: dispatchSync.url(options),
    method: 'post',
})

dispatchSync.form = dispatchSyncForm

const SyncController = { dispatchSync }

export default SyncController