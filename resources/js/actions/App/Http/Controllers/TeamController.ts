import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TeamController::hierarchy
* @see app/Http/Controllers/TeamController.php:201
* @route '/teams/hierarchy'
*/
export const hierarchy = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: hierarchy.url(options),
    method: 'get',
})

hierarchy.definition = {
    methods: ["get","head"],
    url: '/teams/hierarchy',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TeamController::hierarchy
* @see app/Http/Controllers/TeamController.php:201
* @route '/teams/hierarchy'
*/
hierarchy.url = (options?: RouteQueryOptions) => {
    return hierarchy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::hierarchy
* @see app/Http/Controllers/TeamController.php:201
* @route '/teams/hierarchy'
*/
hierarchy.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: hierarchy.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::hierarchy
* @see app/Http/Controllers/TeamController.php:201
* @route '/teams/hierarchy'
*/
hierarchy.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: hierarchy.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TeamController::hierarchy
* @see app/Http/Controllers/TeamController.php:201
* @route '/teams/hierarchy'
*/
const hierarchyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: hierarchy.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::hierarchy
* @see app/Http/Controllers/TeamController.php:201
* @route '/teams/hierarchy'
*/
hierarchyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: hierarchy.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::hierarchy
* @see app/Http/Controllers/TeamController.php:201
* @route '/teams/hierarchy'
*/
hierarchyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: hierarchy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

hierarchy.form = hierarchyForm

/**
* @see \App\Http\Controllers\TeamController::index
* @see app/Http/Controllers/TeamController.php:16
* @route '/teams'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/teams',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TeamController::index
* @see app/Http/Controllers/TeamController.php:16
* @route '/teams'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::index
* @see app/Http/Controllers/TeamController.php:16
* @route '/teams'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::index
* @see app/Http/Controllers/TeamController.php:16
* @route '/teams'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TeamController::index
* @see app/Http/Controllers/TeamController.php:16
* @route '/teams'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::index
* @see app/Http/Controllers/TeamController.php:16
* @route '/teams'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::index
* @see app/Http/Controllers/TeamController.php:16
* @route '/teams'
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

/**
* @see \App\Http\Controllers\TeamController::create
* @see app/Http/Controllers/TeamController.php:96
* @route '/teams/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/teams/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TeamController::create
* @see app/Http/Controllers/TeamController.php:96
* @route '/teams/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::create
* @see app/Http/Controllers/TeamController.php:96
* @route '/teams/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::create
* @see app/Http/Controllers/TeamController.php:96
* @route '/teams/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TeamController::create
* @see app/Http/Controllers/TeamController.php:96
* @route '/teams/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::create
* @see app/Http/Controllers/TeamController.php:96
* @route '/teams/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::create
* @see app/Http/Controllers/TeamController.php:96
* @route '/teams/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\TeamController::store
* @see app/Http/Controllers/TeamController.php:109
* @route '/teams'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/teams',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TeamController::store
* @see app/Http/Controllers/TeamController.php:109
* @route '/teams'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::store
* @see app/Http/Controllers/TeamController.php:109
* @route '/teams'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::store
* @see app/Http/Controllers/TeamController.php:109
* @route '/teams'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::store
* @see app/Http/Controllers/TeamController.php:109
* @route '/teams'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\TeamController::show
* @see app/Http/Controllers/TeamController.php:0
* @route '/teams/{team}'
*/
export const show = (args: { team: string | number } | [team: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/teams/{team}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TeamController::show
* @see app/Http/Controllers/TeamController.php:0
* @route '/teams/{team}'
*/
show.url = (args: { team: string | number } | [team: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { team: args }
    }

    if (Array.isArray(args)) {
        args = {
            team: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        team: args.team,
    }

    return show.definition.url
            .replace('{team}', parsedArgs.team.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::show
* @see app/Http/Controllers/TeamController.php:0
* @route '/teams/{team}'
*/
show.get = (args: { team: string | number } | [team: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::show
* @see app/Http/Controllers/TeamController.php:0
* @route '/teams/{team}'
*/
show.head = (args: { team: string | number } | [team: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TeamController::show
* @see app/Http/Controllers/TeamController.php:0
* @route '/teams/{team}'
*/
const showForm = (args: { team: string | number } | [team: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::show
* @see app/Http/Controllers/TeamController.php:0
* @route '/teams/{team}'
*/
showForm.get = (args: { team: string | number } | [team: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::show
* @see app/Http/Controllers/TeamController.php:0
* @route '/teams/{team}'
*/
showForm.head = (args: { team: string | number } | [team: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\TeamController::edit
* @see app/Http/Controllers/TeamController.php:126
* @route '/teams/{team}/edit'
*/
export const edit = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/teams/{team}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TeamController::edit
* @see app/Http/Controllers/TeamController.php:126
* @route '/teams/{team}/edit'
*/
edit.url = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{team}', parsedArgs.team.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::edit
* @see app/Http/Controllers/TeamController.php:126
* @route '/teams/{team}/edit'
*/
edit.get = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::edit
* @see app/Http/Controllers/TeamController.php:126
* @route '/teams/{team}/edit'
*/
edit.head = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TeamController::edit
* @see app/Http/Controllers/TeamController.php:126
* @route '/teams/{team}/edit'
*/
const editForm = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::edit
* @see app/Http/Controllers/TeamController.php:126
* @route '/teams/{team}/edit'
*/
editForm.get = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TeamController::edit
* @see app/Http/Controllers/TeamController.php:126
* @route '/teams/{team}/edit'
*/
editForm.head = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\TeamController::update
* @see app/Http/Controllers/TeamController.php:148
* @route '/teams/{team}'
*/
export const update = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/teams/{team}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\TeamController::update
* @see app/Http/Controllers/TeamController.php:148
* @route '/teams/{team}'
*/
update.url = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{team}', parsedArgs.team.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::update
* @see app/Http/Controllers/TeamController.php:148
* @route '/teams/{team}'
*/
update.put = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\TeamController::update
* @see app/Http/Controllers/TeamController.php:148
* @route '/teams/{team}'
*/
update.patch = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\TeamController::update
* @see app/Http/Controllers/TeamController.php:148
* @route '/teams/{team}'
*/
const updateForm = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::update
* @see app/Http/Controllers/TeamController.php:148
* @route '/teams/{team}'
*/
updateForm.put = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::update
* @see app/Http/Controllers/TeamController.php:148
* @route '/teams/{team}'
*/
updateForm.patch = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\TeamController::destroy
* @see app/Http/Controllers/TeamController.php:165
* @route '/teams/{team}'
*/
export const destroy = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/teams/{team}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\TeamController::destroy
* @see app/Http/Controllers/TeamController.php:165
* @route '/teams/{team}'
*/
destroy.url = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{team}', parsedArgs.team.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::destroy
* @see app/Http/Controllers/TeamController.php:165
* @route '/teams/{team}'
*/
destroy.delete = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\TeamController::destroy
* @see app/Http/Controllers/TeamController.php:165
* @route '/teams/{team}'
*/
const destroyForm = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::destroy
* @see app/Http/Controllers/TeamController.php:165
* @route '/teams/{team}'
*/
destroyForm.delete = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\TeamController::addMember
* @see app/Http/Controllers/TeamController.php:178
* @route '/teams/{team}/members'
*/
export const addMember = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: addMember.url(args, options),
    method: 'post',
})

addMember.definition = {
    methods: ["post"],
    url: '/teams/{team}/members',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TeamController::addMember
* @see app/Http/Controllers/TeamController.php:178
* @route '/teams/{team}/members'
*/
addMember.url = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return addMember.definition.url
            .replace('{team}', parsedArgs.team.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::addMember
* @see app/Http/Controllers/TeamController.php:178
* @route '/teams/{team}/members'
*/
addMember.post = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: addMember.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::addMember
* @see app/Http/Controllers/TeamController.php:178
* @route '/teams/{team}/members'
*/
const addMemberForm = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: addMember.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::addMember
* @see app/Http/Controllers/TeamController.php:178
* @route '/teams/{team}/members'
*/
addMemberForm.post = (args: { team: number | { id: number } } | [team: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: addMember.url(args, options),
    method: 'post',
})

addMember.form = addMemberForm

/**
* @see \App\Http\Controllers\TeamController::removeMember
* @see app/Http/Controllers/TeamController.php:192
* @route '/teams/{team}/members/{user}'
*/
export const removeMember = (args: { team: number | { id: number }, user: number | { id: number } } | [team: number | { id: number }, user: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeMember.url(args, options),
    method: 'delete',
})

removeMember.definition = {
    methods: ["delete"],
    url: '/teams/{team}/members/{user}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\TeamController::removeMember
* @see app/Http/Controllers/TeamController.php:192
* @route '/teams/{team}/members/{user}'
*/
removeMember.url = (args: { team: number | { id: number }, user: number | { id: number } } | [team: number | { id: number }, user: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            team: args[0],
            user: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        team: typeof args.team === 'object'
        ? args.team.id
        : args.team,
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return removeMember.definition.url
            .replace('{team}', parsedArgs.team.toString())
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TeamController::removeMember
* @see app/Http/Controllers/TeamController.php:192
* @route '/teams/{team}/members/{user}'
*/
removeMember.delete = (args: { team: number | { id: number }, user: number | { id: number } } | [team: number | { id: number }, user: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeMember.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\TeamController::removeMember
* @see app/Http/Controllers/TeamController.php:192
* @route '/teams/{team}/members/{user}'
*/
const removeMemberForm = (args: { team: number | { id: number }, user: number | { id: number } } | [team: number | { id: number }, user: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: removeMember.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TeamController::removeMember
* @see app/Http/Controllers/TeamController.php:192
* @route '/teams/{team}/members/{user}'
*/
removeMemberForm.delete = (args: { team: number | { id: number }, user: number | { id: number } } | [team: number | { id: number }, user: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: removeMember.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

removeMember.form = removeMemberForm

const TeamController = { hierarchy, index, create, store, show, edit, update, destroy, addMember, removeMember }

export default TeamController