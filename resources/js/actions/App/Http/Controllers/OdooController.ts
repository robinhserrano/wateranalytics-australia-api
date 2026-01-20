import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\OdooController::index
* @see app/Http/Controllers/OdooController.php:11
* @route '/odoo-test'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/odoo-test',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OdooController::index
* @see app/Http/Controllers/OdooController.php:11
* @route '/odoo-test'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OdooController::index
* @see app/Http/Controllers/OdooController.php:11
* @route '/odoo-test'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OdooController::index
* @see app/Http/Controllers/OdooController.php:11
* @route '/odoo-test'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\OdooController::index
* @see app/Http/Controllers/OdooController.php:11
* @route '/odoo-test'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OdooController::index
* @see app/Http/Controllers/OdooController.php:11
* @route '/odoo-test'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OdooController::index
* @see app/Http/Controllers/OdooController.php:11
* @route '/odoo-test'
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
* @see \App\Http\Controllers\OdooController::salesOrders
* @see app/Http/Controllers/OdooController.php:46
* @route '/odoo-sales'
*/
export const salesOrders = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: salesOrders.url(options),
    method: 'get',
})

salesOrders.definition = {
    methods: ["get","head"],
    url: '/odoo-sales',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OdooController::salesOrders
* @see app/Http/Controllers/OdooController.php:46
* @route '/odoo-sales'
*/
salesOrders.url = (options?: RouteQueryOptions) => {
    return salesOrders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OdooController::salesOrders
* @see app/Http/Controllers/OdooController.php:46
* @route '/odoo-sales'
*/
salesOrders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: salesOrders.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OdooController::salesOrders
* @see app/Http/Controllers/OdooController.php:46
* @route '/odoo-sales'
*/
salesOrders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: salesOrders.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\OdooController::salesOrders
* @see app/Http/Controllers/OdooController.php:46
* @route '/odoo-sales'
*/
const salesOrdersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: salesOrders.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OdooController::salesOrders
* @see app/Http/Controllers/OdooController.php:46
* @route '/odoo-sales'
*/
salesOrdersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: salesOrders.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\OdooController::salesOrders
* @see app/Http/Controllers/OdooController.php:46
* @route '/odoo-sales'
*/
salesOrdersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: salesOrders.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

salesOrders.form = salesOrdersForm

const OdooController = { index, salesOrders }

export default OdooController