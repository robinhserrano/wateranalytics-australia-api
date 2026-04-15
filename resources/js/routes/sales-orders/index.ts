import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\SalesOrderController::index
* @see app/Http/Controllers/SalesOrderController.php:18
* @route '/sales-orders'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/sales-orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesOrderController::index
* @see app/Http/Controllers/SalesOrderController.php:18
* @route '/sales-orders'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesOrderController::index
* @see app/Http/Controllers/SalesOrderController.php:18
* @route '/sales-orders'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalesOrderController::index
* @see app/Http/Controllers/SalesOrderController.php:18
* @route '/sales-orders'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SalesOrderController::index
* @see app/Http/Controllers/SalesOrderController.php:18
* @route '/sales-orders'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalesOrderController::index
* @see app/Http/Controllers/SalesOrderController.php:18
* @route '/sales-orders'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalesOrderController::index
* @see app/Http/Controllers/SalesOrderController.php:18
* @route '/sales-orders'
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
* @see \App\Http\Controllers\SalesOrderController::show
* @see app/Http/Controllers/SalesOrderController.php:182
* @route '/sales-orders/{sales_order}'
*/
export const show = (args: { sales_order: number | { id: number } } | [sales_order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/sales-orders/{sales_order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesOrderController::show
* @see app/Http/Controllers/SalesOrderController.php:182
* @route '/sales-orders/{sales_order}'
*/
show.url = (args: { sales_order: number | { id: number } } | [sales_order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { sales_order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { sales_order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            sales_order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        sales_order: typeof args.sales_order === 'object'
        ? args.sales_order.id
        : args.sales_order,
    }

    return show.definition.url
            .replace('{sales_order}', parsedArgs.sales_order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesOrderController::show
* @see app/Http/Controllers/SalesOrderController.php:182
* @route '/sales-orders/{sales_order}'
*/
show.get = (args: { sales_order: number | { id: number } } | [sales_order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalesOrderController::show
* @see app/Http/Controllers/SalesOrderController.php:182
* @route '/sales-orders/{sales_order}'
*/
show.head = (args: { sales_order: number | { id: number } } | [sales_order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SalesOrderController::show
* @see app/Http/Controllers/SalesOrderController.php:182
* @route '/sales-orders/{sales_order}'
*/
const showForm = (args: { sales_order: number | { id: number } } | [sales_order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalesOrderController::show
* @see app/Http/Controllers/SalesOrderController.php:182
* @route '/sales-orders/{sales_order}'
*/
showForm.get = (args: { sales_order: number | { id: number } } | [sales_order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SalesOrderController::show
* @see app/Http/Controllers/SalesOrderController.php:182
* @route '/sales-orders/{sales_order}'
*/
showForm.head = (args: { sales_order: number | { id: number } } | [sales_order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const salesOrders = {
    index: Object.assign(index, index),
    show: Object.assign(show, show),
}

export default salesOrders