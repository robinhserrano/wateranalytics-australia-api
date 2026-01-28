import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SyncController::dispatch
* @see [unknown]:0
* @route '/admin/sync'
*/
export const dispatch = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dispatch.url(options),
    method: 'post',
})

dispatch.definition = {
    methods: ["post"],
    url: '/admin/sync',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatch
* @see [unknown]:0
* @route '/admin/sync'
*/
dispatch.url = (options?: RouteQueryOptions) => {
    return dispatch.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatch
* @see [unknown]:0
* @route '/admin/sync'
*/
dispatch.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dispatch.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatch
* @see [unknown]:0
* @route '/admin/sync'
*/
const dispatchForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: dispatch.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\SyncController::dispatch
* @see [unknown]:0
* @route '/admin/sync'
*/
dispatchForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: dispatch.url(options),
    method: 'post',
})

dispatch.form = dispatchForm

const sync = {
    dispatch: Object.assign(dispatch, dispatch),
}

export default sync