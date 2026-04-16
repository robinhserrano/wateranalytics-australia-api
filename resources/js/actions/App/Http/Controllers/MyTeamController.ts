import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\MyTeamController::previewAsManager
* @see app/Http/Controllers/MyTeamController.php:32
* @route '/teams/{team}/view-as-manager'
*/
export const previewAsManager = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAsManager.url(args, options),
    method: 'get',
})

previewAsManager.definition = {
    methods: ["get","head"],
    url: '/teams/{team}/view-as-manager',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MyTeamController::previewAsManager
* @see app/Http/Controllers/MyTeamController.php:32
* @route '/teams/{team}/view-as-manager'
*/
previewAsManager.url = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { team: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { team: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            team: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        team: typeof args.team === 'object'
        ? args.team.id
        : args.team,
    }

    return previewAsManager.definition.url
            .replace('{team}', parsedArgs.team.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MyTeamController::previewAsManager
* @see app/Http/Controllers/MyTeamController.php:32
* @route '/teams/{team}/view-as-manager'
*/
previewAsManager.get = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewAsManager.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MyTeamController::previewAsManager
* @see app/Http/Controllers/MyTeamController.php:32
* @route '/teams/{team}/view-as-manager'
*/
previewAsManager.head = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewAsManager.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MyTeamController::previewAsManager
* @see app/Http/Controllers/MyTeamController.php:32
* @route '/teams/{team}/view-as-manager'
*/
const previewAsManagerForm = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewAsManager.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MyTeamController::previewAsManager
* @see app/Http/Controllers/MyTeamController.php:32
* @route '/teams/{team}/view-as-manager'
*/
previewAsManagerForm.get = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewAsManager.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MyTeamController::previewAsManager
* @see app/Http/Controllers/MyTeamController.php:32
* @route '/teams/{team}/view-as-manager'
*/
previewAsManagerForm.head = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: previewAsManager.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

previewAsManager.form = previewAsManagerForm

/**
* @see \App\Http\Controllers\MyTeamController::index
* @see app/Http/Controllers/MyTeamController.php:17
* @route '/my-team'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/my-team',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MyTeamController::index
* @see app/Http/Controllers/MyTeamController.php:17
* @route '/my-team'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MyTeamController::index
* @see app/Http/Controllers/MyTeamController.php:17
* @route '/my-team'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MyTeamController::index
* @see app/Http/Controllers/MyTeamController.php:17
* @route '/my-team'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MyTeamController::index
* @see app/Http/Controllers/MyTeamController.php:17
* @route '/my-team'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MyTeamController::index
* @see app/Http/Controllers/MyTeamController.php:17
* @route '/my-team'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MyTeamController::index
* @see app/Http/Controllers/MyTeamController.php:17
* @route '/my-team'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

const MyTeamController = { previewAsManager, index }

export default MyTeamController