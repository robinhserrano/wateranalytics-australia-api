import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ProductStockController::index
* @see app/Http/Controllers/ProductStockController.php:12
* @route '/stocks'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/stocks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProductStockController::index
* @see app/Http/Controllers/ProductStockController.php:12
* @route '/stocks'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProductStockController::index
* @see app/Http/Controllers/ProductStockController.php:12
* @route '/stocks'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProductStockController::index
* @see app/Http/Controllers/ProductStockController.php:12
* @route '/stocks'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProductStockController::index
* @see app/Http/Controllers/ProductStockController.php:12
* @route '/stocks'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProductStockController::index
* @see app/Http/Controllers/ProductStockController.php:12
* @route '/stocks'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProductStockController::index
* @see app/Http/Controllers/ProductStockController.php:12
* @route '/stocks'
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
* @see \App\Http\Controllers\ProductStockController::show
* @see app/Http/Controllers/ProductStockController.php:50
* @route '/stocks/{stock}'
*/
export const show = (args: { stock: string | number } | [stock: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/stocks/{stock}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProductStockController::show
* @see app/Http/Controllers/ProductStockController.php:50
* @route '/stocks/{stock}'
*/
show.url = (args: { stock: string | number } | [stock: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stock: args }
    }

    if (Array.isArray(args)) {
        args = {
            stock: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        stock: args.stock,
    }

    return show.definition.url
            .replace('{stock}', parsedArgs.stock.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProductStockController::show
* @see app/Http/Controllers/ProductStockController.php:50
* @route '/stocks/{stock}'
*/
show.get = (args: { stock: string | number } | [stock: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProductStockController::show
* @see app/Http/Controllers/ProductStockController.php:50
* @route '/stocks/{stock}'
*/
show.head = (args: { stock: string | number } | [stock: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProductStockController::show
* @see app/Http/Controllers/ProductStockController.php:50
* @route '/stocks/{stock}'
*/
const showForm = (args: { stock: string | number } | [stock: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProductStockController::show
* @see app/Http/Controllers/ProductStockController.php:50
* @route '/stocks/{stock}'
*/
showForm.get = (args: { stock: string | number } | [stock: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProductStockController::show
* @see app/Http/Controllers/ProductStockController.php:50
* @route '/stocks/{stock}'
*/
showForm.head = (args: { stock: string | number } | [stock: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const ProductStockController = { index, show }

export default ProductStockController