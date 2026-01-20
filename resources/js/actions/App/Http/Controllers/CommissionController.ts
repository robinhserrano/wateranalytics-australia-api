import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
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
export const show = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
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
show.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
show.get = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
show.head = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
const showForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
showForm.get = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::show
* @see app/Http/Controllers/CommissionController.php:121
* @route '/commissions/{commission}'
*/
showForm.head = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
export const recalculate = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
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
recalculate.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
recalculate.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculate.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::recalculate
* @see app/Http/Controllers/CommissionController.php:169
* @route '/commissions/{commission}/recalculate'
*/
const recalculateForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: recalculate.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::recalculate
* @see app/Http/Controllers/CommissionController.php:169
* @route '/commissions/{commission}/recalculate'
*/
recalculateForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: recalculate.url(args, options),
    method: 'post',
})

recalculate.form = recalculateForm

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
export const adjust = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
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
adjust.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
adjust.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
const adjustForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: adjust.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::adjust
* @see app/Http/Controllers/CommissionController.php:185
* @route '/commissions/{commission}/adjust'
*/
adjustForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: adjust.url(args, options),
    method: 'post',
})

adjust.form = adjustForm

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
export const approve = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
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
approve.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
approve.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
const approveForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::approve
* @see app/Http/Controllers/CommissionController.php:209
* @route '/commissions/{commission}/approve'
*/
approveForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

approve.form = approveForm

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
export const reject = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
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
reject.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
reject.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
const rejectForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::reject
* @see app/Http/Controllers/CommissionController.php:243
* @route '/commissions/{commission}/reject'
*/
rejectForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reject.url(args, options),
    method: 'post',
})

reject.form = rejectForm

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
export const confirm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
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
confirm.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
confirm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
const confirmForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::confirm
* @see app/Http/Controllers/CommissionController.php:278
* @route '/commissions/{commission}/confirm'
*/
confirmForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirm.url(args, options),
    method: 'post',
})

confirm.form = confirmForm

/**
* @see \App\Http\Controllers\CommissionController::markAsPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
export const markAsPaid = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAsPaid.url(args, options),
    method: 'post',
})

markAsPaid.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/mark-paid',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::markAsPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
markAsPaid.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return markAsPaid.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::markAsPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
markAsPaid.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAsPaid.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markAsPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
const markAsPaidForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markAsPaid.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markAsPaid
* @see app/Http/Controllers/CommissionController.php:310
* @route '/commissions/{commission}/mark-paid'
*/
markAsPaidForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markAsPaid.url(args, options),
    method: 'post',
})

markAsPaid.form = markAsPaidForm

/**
* @see \App\Http\Controllers\CommissionController::markAsEnteredToOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
export const markAsEnteredToOdoo = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAsEnteredToOdoo.url(args, options),
    method: 'post',
})

markAsEnteredToOdoo.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/mark-odoo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::markAsEnteredToOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
markAsEnteredToOdoo.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return markAsEnteredToOdoo.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::markAsEnteredToOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
markAsEnteredToOdoo.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAsEnteredToOdoo.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markAsEnteredToOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
const markAsEnteredToOdooForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markAsEnteredToOdoo.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::markAsEnteredToOdoo
* @see app/Http/Controllers/CommissionController.php:338
* @route '/commissions/{commission}/mark-odoo'
*/
markAsEnteredToOdooForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markAsEnteredToOdoo.url(args, options),
    method: 'post',
})

markAsEnteredToOdoo.form = markAsEnteredToOdooForm

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
export const resetConfirm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
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
resetConfirm.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
resetConfirm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetConfirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
const resetConfirmForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetConfirm.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetConfirm
* @see app/Http/Controllers/CommissionController.php:366
* @route '/commissions/{commission}/reset-confirm'
*/
resetConfirmForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetConfirm.url(args, options),
    method: 'post',
})

resetConfirm.form = resetConfirmForm

/**
* @see \App\Http\Controllers\CommissionController::resetOdooSync
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
export const resetOdooSync = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetOdooSync.url(args, options),
    method: 'post',
})

resetOdooSync.definition = {
    methods: ["post"],
    url: '/commissions/{commission}/reset-odoo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CommissionController::resetOdooSync
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
resetOdooSync.url = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return resetOdooSync.definition.url
            .replace('{commission}', parsedArgs.commission.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::resetOdooSync
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
resetOdooSync.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetOdooSync.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetOdooSync
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
const resetOdooSyncForm = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetOdooSync.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CommissionController::resetOdooSync
* @see app/Http/Controllers/CommissionController.php:399
* @route '/commissions/{commission}/reset-odoo'
*/
resetOdooSyncForm.post = (args: { commission: string | number | { id: string | number } } | [commission: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetOdooSync.url(args, options),
    method: 'post',
})

resetOdooSync.form = resetOdooSyncForm

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

/**
* @see \App\Http\Controllers\CommissionController::userStats
* @see app/Http/Controllers/CommissionController.php:475
* @route '/users/{user}/commission-stats'
*/
export const userStats = (args: { user: string | number } | [user: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: userStats.url(args, options),
    method: 'get',
})

userStats.definition = {
    methods: ["get","head"],
    url: '/users/{user}/commission-stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CommissionController::userStats
* @see app/Http/Controllers/CommissionController.php:475
* @route '/users/{user}/commission-stats'
*/
userStats.url = (args: { user: string | number } | [user: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: args.user,
    }

    return userStats.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CommissionController::userStats
* @see app/Http/Controllers/CommissionController.php:475
* @route '/users/{user}/commission-stats'
*/
userStats.get = (args: { user: string | number } | [user: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: userStats.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::userStats
* @see app/Http/Controllers/CommissionController.php:475
* @route '/users/{user}/commission-stats'
*/
userStats.head = (args: { user: string | number } | [user: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: userStats.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CommissionController::userStats
* @see app/Http/Controllers/CommissionController.php:475
* @route '/users/{user}/commission-stats'
*/
const userStatsForm = (args: { user: string | number } | [user: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: userStats.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::userStats
* @see app/Http/Controllers/CommissionController.php:475
* @route '/users/{user}/commission-stats'
*/
userStatsForm.get = (args: { user: string | number } | [user: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: userStats.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CommissionController::userStats
* @see app/Http/Controllers/CommissionController.php:475
* @route '/users/{user}/commission-stats'
*/
userStatsForm.head = (args: { user: string | number } | [user: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: userStats.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

userStats.form = userStatsForm

const CommissionController = { index, show, calculate, recalculate, adjust, approve, reject, confirm, markAsPaid, markAsEnteredToOdoo, resetConfirm, resetOdooSync, bulkApprove, userStats }

export default CommissionController