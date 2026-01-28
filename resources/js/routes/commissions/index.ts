import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\CommissionController::index
* @see app/Http/Controllers/CommissionController.php:26
* @route '/commissions'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/commissions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CommissionController::index
* @see app/Http/Controllers/CommissionController.php:26
* @route '/commissions'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::index
* @see app/Http/Controllers/CommissionController.php:26
* @route '/commissions'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::index
* @see app/Http/Controllers/CommissionController.php:26
* @route '/commissions'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CommissionController::index
* @see app/Http/Controllers/CommissionController.php:26
* @route '/commissions'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::index
* @see app/Http/Controllers/CommissionController.php:26
* @route '/commissions'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::index
* @see app/Http/Controllers/CommissionController.php:26
* @route '/commissions'
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
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
export const show = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/commissions/{commission}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
show.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return show.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
show.get = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
show.head = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
const showForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
showForm.get = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
showForm.head = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\CommissionController::calculate
* @see app/Http/Controllers/CommissionController.php:142
* @route '/commissions/calculate'
*/
export const calculate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: calculate.url(options),
    method: 'post',
})

calculate.definition = {
    methods: ["post"],
    url: '/commissions/calculate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::calculate
* @see app/Http/Controllers/CommissionController.php:142
* @route '/commissions/calculate'
*/
calculate.url = (options?: RouteQueryOptions) => {
    return calculate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::calculate
* @see app/Http/Controllers/CommissionController.php:142
* @route '/commissions/calculate'
*/
calculate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: calculate.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::calculate
* @see app/Http/Controllers/CommissionController.php:142
* @route '/commissions/calculate'
*/
const calculateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: calculate.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::calculate
* @see app/Http/Controllers/CommissionController.php:142
* @route '/commissions/calculate'
*/
calculateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: calculate.url(options),
    method: 'post',
})

calculate.form = calculateForm

/**
* @see \App\Http\Controllers\CommissionController::recalculate
* @see app/Http/Controllers/CommissionController.php:169
* @route '/commissions/{commission}/recalculate'
*/
export const recalculate = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculate.url(args, options),
    method: 'post',
})

recalculate.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/recalculate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::recalculate
* @see app/Http/Controllers/CommissionController.php:169
* @route '/commissions/{commission}/recalculate'
*/
recalculate.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return recalculate.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::recalculate
* @see app/Http/Controllers/CommissionController.php:169
* @route '/commissions/{commission}/recalculate'
*/
recalculate.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculate.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::recalculate
* @see app/Http/Controllers/CommissionController.php:169
* @route '/commissions/{commission}/recalculate'
*/
const recalculateForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: recalculate.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::recalculate
* @see app/Http/Controllers/CommissionController.php:169
* @route '/commissions/{commission}/recalculate'
*/
recalculateForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: recalculate.url(args, options),
    method: 'post',
})

recalculate.form = recalculateForm

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
export const adjust = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(args, options),
    method: 'post',
})

adjust.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/adjust',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
adjust.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return adjust.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
adjust.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
const adjustForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: adjust.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
adjustForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: adjust.url(args, options),
    method: 'post',
})

adjust.form = adjustForm

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
export const approve = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
approve.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return approve.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
approve.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
const approveForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
approveForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

approve.form = approveForm

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
export const reject = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
reject.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return reject.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
reject.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
const rejectForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
rejectForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

reject.form = rejectForm

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
export const confirm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(args, options),
    method: 'post',
})

confirm.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/confirm',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
confirm.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return confirm.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
confirm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
const confirmForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
confirmForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirm.url(args, options),
    method: 'post',
})

confirm.form = confirmForm

/**
* @see \App\Http\Controllers\CommissionController::markPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
export const markPaid = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markPaid.url(args, options),
    method: 'post',
})

markPaid.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/mark-paid',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::markPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
markPaid.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return markPaid.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::markPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
markPaid.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markPaid.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
const markPaidForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markPaid.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
markPaidForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markPaid.url(args, options),
    method: 'post',
})

markPaid.form = markPaidForm

/**
* @see \App\Http\Controllers\CommissionController::markOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
export const markOdoo = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markOdoo.url(args, options),
    method: 'post',
})

markOdoo.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/mark-odoo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::markOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
markOdoo.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return markOdoo.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::markOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
markOdoo.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markOdoo.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
const markOdooForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markOdoo.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
markOdooForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markOdoo.url(args, options),
    method: 'post',
})

markOdoo.form = markOdooForm

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
export const resetConfirm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetConfirm.url(args, options),
    method: 'post',
})

resetConfirm.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/reset-confirm',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
resetConfirm.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return resetConfirm.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
resetConfirm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetConfirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
const resetConfirmForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetConfirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
resetConfirmForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetConfirm.url(args, options),
    method: 'post',
})

resetConfirm.form = resetConfirmForm

/**
* @see \App\Http\Controllers\CommissionController::resetOdoo
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
export const resetOdoo = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetOdoo.url(args, options),
    method: 'post',
})

resetOdoo.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/reset-odoo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::resetOdoo
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
resetOdoo.url = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { commission: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { commission: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            commission: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        commission: typeof args.commission === 'object'
        ? args.commission.id
        : args.commission,
    }

    return resetOdoo.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::resetOdoo
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
resetOdoo.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetOdoo.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetOdoo
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
const resetOdooForm = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetOdoo.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetOdoo
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
resetOdooForm.post = (args: { commission: number | { id: number } } | [commission: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetOdoo.url(args, options),
    method: 'post',
})

resetOdoo.form = resetOdooForm

/**
* @see \App\Http\Controllers\CommissionController::bulkApprove
* @see app/Http/Controllers/CommissionController.php:431
* @route '/commissions/bulk-approve'
*/
export const bulkApprove = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove.url(options),
    method: 'post',
})

bulkApprove.definition = {
    methods: ["post"],
    url: '/commissions/bulk-approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::bulkApprove
* @see app/Http/Controllers/CommissionController.php:431
* @route '/commissions/bulk-approve'
*/
bulkApprove.url = (options?: RouteQueryOptions) => {
    return bulkApprove.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::bulkApprove
* @see app/Http/Controllers/CommissionController.php:431
* @route '/commissions/bulk-approve'
*/
bulkApprove.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkApprove.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::bulkApprove
* @see app/Http/Controllers/CommissionController.php:431
* @route '/commissions/bulk-approve'
*/
const bulkApproveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkApprove.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::bulkApprove
* @see app/Http/Controllers/CommissionController.php:431
* @route '/commissions/bulk-approve'
*/
bulkApproveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: bulkApprove.url(options),
    method: 'post',
})

bulkApprove.form = bulkApproveForm

const commissions = {
    index: Object.assign(index, index),
    show: Object.assign(show, show),
    calculate: Object.assign(calculate, calculate),
    recalculate: Object.assign(recalculate, recalculate),
    adjust: Object.assign(adjust, adjust),
    approve: Object.assign(approve, approve),
    reject: Object.assign(reject, reject),
    confirm: Object.assign(confirm, confirm),
    markPaid: Object.assign(markPaid, markPaid),
    markOdoo: Object.assign(markOdoo, markOdoo),
    resetConfirm: Object.assign(resetConfirm, resetConfirm),
    resetOdoo: Object.assign(resetOdoo, resetOdoo),
    bulkApprove: Object.assign(bulkApprove, bulkApprove),
}

export default commissions