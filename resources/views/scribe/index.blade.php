<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Laravel API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://wateranalytics-australia-api.test";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.6.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.6.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-GETapi-user">
                                <a href="#endpoints-GETapi-user">GET api/user</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-sales-orders" class="tocify-header">
                <li class="tocify-item level-1" data-unique="sales-orders">
                    <a href="#sales-orders">Sales Orders</a>
                </li>
                                    <ul id="tocify-subheader-sales-orders" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="sales-orders-GETapi-sales-orders">
                                <a href="#sales-orders-GETapi-sales-orders">List Sales Orders</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="sales-orders-GETapi-sales-orders--id-">
                                <a href="#sales-orders-GETapi-sales-orders--id-">Get Sales Order</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: December 11, 2025</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://wateranalytics-australia-api.test</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-GETapi-user">GET api/user</h2>

<p>
</p>



<span id="example-requests-GETapi-user">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://wateranalytics-australia-api.test/api/user" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://wateranalytics-australia-api.test/api/user"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-user">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-user" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-user"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-user"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-user" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-user">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-user" data-method="GET"
      data-path="api/user"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-user', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-user"
                    onclick="tryItOut('GETapi-user');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-user"
                    onclick="cancelTryOut('GETapi-user');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-user"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/user</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="sales-orders">Sales Orders</h1>

    <p>APIs for managing sales orders</p>

                                <h2 id="sales-orders-GETapi-sales-orders">List Sales Orders</h2>

<p>
</p>

<p>Get a paginated list of sales orders with their order lines.</p>

<span id="example-requests-GETapi-sales-orders">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://wateranalytics-australia-api.test/api/sales-orders?page=1&amp;per_page=15" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://wateranalytics-australia-api.test/api/sales-orders"
);

const params = {
    "page": "1",
    "per_page": "15",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-sales-orders">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;current_page&quot;: 1,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 4860,
            &quot;odoo_id&quot;: 12413,
            &quot;name&quot;: &quot;S12409&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T02:44:00.000000Z&quot;,
            &quot;partner_id&quot;: 6422,
            &quot;partner_name&quot;: &quot;James Huxtable&quot;,
            &quot;user_id&quot;: 572,
            &quot;user_name&quot;: &quot;Bronwyn Parnell&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;4200.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;4200.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33320,
                33321
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 381.82,
                                &quot;tax_amount&quot;: 381.82,
                                &quot;base_amount_currency&quot;: 3818.18,
                                &quot;base_amount&quot;: 3818.18,
                                &quot;display_base_amount_currency&quot;: 3818.18,
                                &quot;display_base_amount&quot;: 3818.18,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 381.82,
                        &quot;tax_amount&quot;: 381.82,
                        &quot;base_amount_currency&quot;: 3818.18,
                        &quot;base_amount&quot;: 3818.18,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 3818.18,
                &quot;base_amount&quot;: 3818.18,
                &quot;tax_amount_currency&quot;: 381.82,
                &quot;tax_amount&quot;: 381.82,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 4200,
                &quot;total_amount&quot;: 4200
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 15481,
                    &quot;odoo_id&quot;: 33320,
                    &quot;sales_order_id&quot;: 4860,
                    &quot;odoo_order_id&quot;: 12413,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;4200.00&quot;,
                    &quot;price_subtotal&quot;: &quot;3818.18&quot;,
                    &quot;price_total&quot;: &quot;4200.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 15482,
                    &quot;odoo_id&quot;: 33321,
                    &quot;sales_order_id&quot;: 4860,
                    &quot;odoo_order_id&quot;: 12413,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 4858,
            &quot;odoo_id&quot;: 12412,
            &quot;name&quot;: &quot;S12408&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T02:35:57.000000Z&quot;,
            &quot;partner_id&quot;: 6421,
            &quot;partner_name&quot;: &quot;Samad Ansari&quot;,
            &quot;user_id&quot;: 572,
            &quot;user_name&quot;: &quot;Bronwyn Parnell&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;S07595&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;3220.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;3220.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p&gt;Supply Only - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-Black (w filters) - Qty: 1.0&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33318,
                33319
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 292.73,
                                &quot;tax_amount&quot;: 292.73,
                                &quot;base_amount_currency&quot;: 2927.27,
                                &quot;base_amount&quot;: 2927.27,
                                &quot;display_base_amount_currency&quot;: 2927.27,
                                &quot;display_base_amount&quot;: 2927.27,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 292.73,
                        &quot;tax_amount&quot;: 292.73,
                        &quot;base_amount_currency&quot;: 2927.27,
                        &quot;base_amount&quot;: 2927.27,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 2927.27,
                &quot;base_amount&quot;: 2927.27,
                &quot;tax_amount_currency&quot;: 292.73,
                &quot;tax_amount&quot;: 292.73,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 3220,
                &quot;total_amount&quot;: 3220
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:39:38.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;odoo_id&quot;: 33318,
                    &quot;sales_order_id&quot;: 4858,
                    &quot;odoo_order_id&quot;: 12412,
                    &quot;product_id&quot;: 46,
                    &quot;product_name&quot;: &quot;Supply Only&quot;,
                    &quot;name&quot;: &quot;Supply Only&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;3220.00&quot;,
                    &quot;price_subtotal&quot;: &quot;2927.27&quot;,
                    &quot;price_total&quot;: &quot;3220.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 2,
                    &quot;odoo_id&quot;: 33319,
                    &quot;sales_order_id&quot;: 4858,
                    &quot;odoo_order_id&quot;: 12412,
                    &quot;product_id&quot;: 1,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)\nBlack/Teal&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 4859,
            &quot;odoo_id&quot;: 12411,
            &quot;name&quot;: &quot;S12407&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T02:30:19.000000Z&quot;,
            &quot;partner_id&quot;: 6420,
            &quot;partner_name&quot;: &quot;Karan Bhudia&quot;,
            &quot;user_id&quot;: 572,
            &quot;user_name&quot;: &quot;Bronwyn Parnell&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;4900.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;4900.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7624G (U Detachable) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;1.jpg\&quot; src=\&quot;/web/image/290093-1b1d47ae/1.jpg?access_token=819353ee-4313-459b-899e-ba696b04c5f5\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;2.jpg\&quot; src=\&quot;/web/image/290094-6482982f/2.jpg?access_token=55f84fee-a924-4799-bd2d-3959e32534d0\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;3.jpg\&quot; src=\&quot;/web/image/290092-0c37442d/3.jpg?access_token=ae0346e0-9be9-4668-8f25-00e99bb4c724\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33314,
                33315,
                33316,
                33317
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 445.45,
                                &quot;tax_amount&quot;: 445.45,
                                &quot;base_amount_currency&quot;: 4454.55,
                                &quot;base_amount&quot;: 4454.55,
                                &quot;display_base_amount_currency&quot;: 4454.55,
                                &quot;display_base_amount&quot;: 4454.55,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 445.45,
                        &quot;tax_amount&quot;: 445.45,
                        &quot;base_amount_currency&quot;: 4454.55,
                        &quot;base_amount&quot;: 4454.55,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 4454.55,
                &quot;base_amount&quot;: 4454.55,
                &quot;tax_amount_currency&quot;: 445.45,
                &quot;tax_amount&quot;: 445.45,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 4900,
                &quot;total_amount&quot;: 4900
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:39:38.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 3,
                    &quot;odoo_id&quot;: 33314,
                    &quot;sales_order_id&quot;: 4859,
                    &quot;odoo_order_id&quot;: 12411,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;4900.00&quot;,
                    &quot;price_subtotal&quot;: &quot;4454.55&quot;,
                    &quot;price_total&quot;: &quot;4900.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 4,
                    &quot;odoo_id&quot;: 33315,
                    &quot;sales_order_id&quot;: 4859,
                    &quot;odoo_order_id&quot;: 12411,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 5,
                    &quot;odoo_id&quot;: 33316,
                    &quot;sales_order_id&quot;: 4859,
                    &quot;odoo_order_id&quot;: 12411,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 6,
                    &quot;odoo_id&quot;: 33317,
                    &quot;sales_order_id&quot;: 4859,
                    &quot;odoo_order_id&quot;: 12411,
                    &quot;product_id&quot;: 37,
                    &quot;product_name&quot;: &quot;[3WM 7624G] 3 Way Mixer 7624G (U Detachable)&quot;,
                    &quot;name&quot;: &quot;[3WM 7624G] 3 Way Mixer 7624G (U Detachable)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 1,
            &quot;odoo_id&quot;: 12409,
            &quot;name&quot;: &quot;S12405&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T01:29:13.000000Z&quot;,
            &quot;partner_id&quot;: 6419,
            &quot;partner_name&quot;: &quot;Yul Brisset&quot;,
            &quot;user_id&quot;: 367,
            &quot;user_name&quot;: &quot;LINKTRADE INTERNATIONAL PTY LTD (Kapila De Silva)&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;4890.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;4890.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7624S (U Detachable) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;image000000.JPG\&quot; src=\&quot;/web/image/290000-383944d8/image000000.JPG?access_token=45657b10-2f17-4b15-beaa-df41fa1295c0\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;image000001.JPG\&quot; src=\&quot;/web/image/290001-4eb2d2f5/image000001.JPG?access_token=6443b67d-909d-4df0-8e79-94386344ff38\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;image000002.JPG\&quot; src=\&quot;/web/image/290002-57645e82/image000002.JPG?access_token=58443aa2-0410-4fd1-8520-d09af32fac09\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33305,
                33306,
                33307,
                33308
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 444.55,
                                &quot;tax_amount&quot;: 444.55,
                                &quot;base_amount_currency&quot;: 4445.45,
                                &quot;base_amount&quot;: 4445.45,
                                &quot;display_base_amount_currency&quot;: 4445.45,
                                &quot;display_base_amount&quot;: 4445.45,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 444.55,
                        &quot;tax_amount&quot;: 444.55,
                        &quot;base_amount_currency&quot;: 4445.45,
                        &quot;base_amount&quot;: 4445.45,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 4445.45,
                &quot;base_amount&quot;: 4445.45,
                &quot;tax_amount_currency&quot;: 444.55,
                &quot;tax_amount&quot;: 444.55,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 4890,
                &quot;total_amount&quot;: 4890
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:09:55.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 7,
                    &quot;odoo_id&quot;: 33305,
                    &quot;sales_order_id&quot;: 1,
                    &quot;odoo_order_id&quot;: 12409,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;4890.00&quot;,
                    &quot;price_subtotal&quot;: &quot;4445.45&quot;,
                    &quot;price_total&quot;: &quot;4890.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 8,
                    &quot;odoo_id&quot;: 33306,
                    &quot;sales_order_id&quot;: 1,
                    &quot;odoo_order_id&quot;: 12409,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 9,
                    &quot;odoo_id&quot;: 33307,
                    &quot;sales_order_id&quot;: 1,
                    &quot;odoo_order_id&quot;: 12409,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 10,
                    &quot;odoo_id&quot;: 33308,
                    &quot;sales_order_id&quot;: 1,
                    &quot;odoo_order_id&quot;: 12409,
                    &quot;product_id&quot;: 38,
                    &quot;product_name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
                    &quot;name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 2,
            &quot;odoo_id&quot;: 12408,
            &quot;name&quot;: &quot;S12404&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T01:22:39.000000Z&quot;,
            &quot;partner_id&quot;: 6418,
            &quot;partner_name&quot;: &quot;Pubudu Wijesinghe&quot;,
            &quot;user_id&quot;: 367,
            &quot;user_name&quot;: &quot;LINKTRADE INTERNATIONAL PTY LTD (Kapila De Silva)&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Self Gen&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;S03564&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;4890.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;4890.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7606S (L Flat) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_4504.jpeg\&quot; src=\&quot;/web/image/289983-4005cd05/IMG_4504.jpeg?access_token=23a0bd4c-ddb3-4eb3-aff5-68f7a5e2f167\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_4505.jpeg\&quot; src=\&quot;/web/image/289984-066d1a78/IMG_4505.jpeg?access_token=6bc07ba1-82f7-4e6a-a882-3d0b379739bc\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_4506.jpeg\&quot; src=\&quot;/web/image/289985-68414135/IMG_4506.jpeg?access_token=0c30608b-07a0-497f-b984-80bc25075022\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33301,
                33302,
                33303,
                33304
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 444.55,
                                &quot;tax_amount&quot;: 444.55,
                                &quot;base_amount_currency&quot;: 4445.45,
                                &quot;base_amount&quot;: 4445.45,
                                &quot;display_base_amount_currency&quot;: 4445.45,
                                &quot;display_base_amount&quot;: 4445.45,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 444.55,
                        &quot;tax_amount&quot;: 444.55,
                        &quot;base_amount_currency&quot;: 4445.45,
                        &quot;base_amount&quot;: 4445.45,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 4445.45,
                &quot;base_amount&quot;: 4445.45,
                &quot;tax_amount_currency&quot;: 444.55,
                &quot;tax_amount&quot;: 444.55,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 4890,
                &quot;total_amount&quot;: 4890
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 11,
                    &quot;odoo_id&quot;: 33301,
                    &quot;sales_order_id&quot;: 2,
                    &quot;odoo_order_id&quot;: 12408,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;4890.00&quot;,
                    &quot;price_subtotal&quot;: &quot;4445.45&quot;,
                    &quot;price_total&quot;: &quot;4890.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 12,
                    &quot;odoo_id&quot;: 33302,
                    &quot;sales_order_id&quot;: 2,
                    &quot;odoo_order_id&quot;: 12408,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 13,
                    &quot;odoo_id&quot;: 33303,
                    &quot;sales_order_id&quot;: 2,
                    &quot;odoo_order_id&quot;: 12408,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 14,
                    &quot;odoo_id&quot;: 33304,
                    &quot;sales_order_id&quot;: 2,
                    &quot;odoo_order_id&quot;: 12408,
                    &quot;product_id&quot;: 35,
                    &quot;product_name&quot;: &quot;[3WM 7606S] 3 Way Mixer 7606S (L Flat)&quot;,
                    &quot;name&quot;: &quot;[3WM 7606S] 3 Way Mixer 7606S (L Flat)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 3,
            &quot;odoo_id&quot;: 12407,
            &quot;name&quot;: &quot;S12403&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T00:56:05.000000Z&quot;,
            &quot;partner_id&quot;: 6417,
            &quot;partner_name&quot;: &quot;Diane Flynn&quot;,
            &quot;user_id&quot;: 494,
            &quot;user_name&quot;: &quot;Brad Sumpter&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Self Gen&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Finance - Brighte&quot;,
            &quot;amount_total&quot;: &quot;5490.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;5490.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7624S (U Detachable) - Qty: 1.0&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33297,
                33298,
                33299,
                33300
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 499.09,
                                &quot;tax_amount&quot;: 499.09,
                                &quot;base_amount_currency&quot;: 4990.91,
                                &quot;base_amount&quot;: 4990.91,
                                &quot;display_base_amount_currency&quot;: 4990.91,
                                &quot;display_base_amount&quot;: 4990.91,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 499.09,
                        &quot;tax_amount&quot;: 499.09,
                        &quot;base_amount_currency&quot;: 4990.91,
                        &quot;base_amount&quot;: 4990.91,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 4990.91,
                &quot;base_amount&quot;: 4990.91,
                &quot;tax_amount_currency&quot;: 499.09,
                &quot;tax_amount&quot;: 499.09,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 5490,
                &quot;total_amount&quot;: 5490
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 15,
                    &quot;odoo_id&quot;: 33297,
                    &quot;sales_order_id&quot;: 3,
                    &quot;odoo_order_id&quot;: 12407,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;5490.00&quot;,
                    &quot;price_subtotal&quot;: &quot;4990.91&quot;,
                    &quot;price_total&quot;: &quot;5490.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 16,
                    &quot;odoo_id&quot;: 33298,
                    &quot;sales_order_id&quot;: 3,
                    &quot;odoo_order_id&quot;: 12407,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 17,
                    &quot;odoo_id&quot;: 33299,
                    &quot;sales_order_id&quot;: 3,
                    &quot;odoo_order_id&quot;: 12407,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 18,
                    &quot;odoo_id&quot;: 33300,
                    &quot;sales_order_id&quot;: 3,
                    &quot;odoo_order_id&quot;: 12407,
                    &quot;product_id&quot;: 38,
                    &quot;product_name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
                    &quot;name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 4,
            &quot;odoo_id&quot;: 12406,
            &quot;name&quot;: &quot;S12402&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T00:49:21.000000Z&quot;,
            &quot;partner_id&quot;: 6416,
            &quot;partner_name&quot;: &quot;Jomon Antony&quot;,
            &quot;user_id&quot;: 389,
            &quot;user_name&quot;: &quot;Sam Tornatore&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Self Gen&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;4190.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;4190.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-Black (w filters) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_9714.PNG\&quot; src=\&quot;/web/image/289895-113c74b3/IMG_9714.PNG?access_token=ce81b11d-746e-495d-85eb-875aac1e942e\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_9715.PNG\&quot; src=\&quot;/web/image/289897-0e7d3b17/IMG_9715.PNG?access_token=7600dfa5-952e-411a-a85f-4c847862cd93\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_9716.PNG\&quot; src=\&quot;/web/image/289898-2dea5c5d/IMG_9716.PNG?access_token=007e708a-688c-469a-88f9-481606666342\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33295,
                33296
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 380.91,
                                &quot;tax_amount&quot;: 380.91,
                                &quot;base_amount_currency&quot;: 3809.09,
                                &quot;base_amount&quot;: 3809.09,
                                &quot;display_base_amount_currency&quot;: 3809.09,
                                &quot;display_base_amount&quot;: 3809.09,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 380.91,
                        &quot;tax_amount&quot;: 380.91,
                        &quot;base_amount_currency&quot;: 3809.09,
                        &quot;base_amount&quot;: 3809.09,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 3809.09,
                &quot;base_amount&quot;: 3809.09,
                &quot;tax_amount_currency&quot;: 380.91,
                &quot;tax_amount&quot;: 380.91,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 4190,
                &quot;total_amount&quot;: 4190
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 19,
                    &quot;odoo_id&quot;: 33295,
                    &quot;sales_order_id&quot;: 4,
                    &quot;odoo_order_id&quot;: 12406,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;4190.00&quot;,
                    &quot;price_subtotal&quot;: &quot;3809.09&quot;,
                    &quot;price_total&quot;: &quot;4190.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 20,
                    &quot;odoo_id&quot;: 33296,
                    &quot;sales_order_id&quot;: 4,
                    &quot;odoo_order_id&quot;: 12406,
                    &quot;product_id&quot;: 1,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)\nBlack/Teal&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 5,
            &quot;odoo_id&quot;: 12404,
            &quot;name&quot;: &quot;S12400&quot;,
            &quot;create_date&quot;: &quot;2025-12-10T00:20:27.000000Z&quot;,
            &quot;partner_id&quot;: 6415,
            &quot;partner_name&quot;: &quot;Inderjeet Singh&quot;,
            &quot;user_id&quot;: 9,
            &quot;user_name&quot;: &quot;Jerry Zhu&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;3000.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;3000.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-Black (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7605S (U) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-09 at 14.40.14 (1).jpeg\&quot; src=\&quot;/web/image/289842-026fb455/WhatsApp%20Image%202025-12-09%20at%2014.40.14%20%281%29.jpeg?access_token=6f5531e4-9645-463a-b4f1-eec61243f42b\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-09 at 14.40.14 (2).jpeg\&quot; src=\&quot;/web/image/289845-32a2940c/WhatsApp%20Image%202025-12-09%20at%2014.40.14%20%282%29.jpeg?access_token=b4eeca0b-0f1a-4f6e-b596-f410620cad37\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-09 at 14.40.14.jpeg\&quot; src=\&quot;/web/image/289844-609e66f0/WhatsApp%20Image%202025-12-09%20at%2014.40.14.jpeg?access_token=1dab9867-84b9-43b9-8023-bd4d3fcb9aaf\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-09 at 14.40.15.jpeg\&quot; src=\&quot;/web/image/289843-bb0639da/WhatsApp%20Image%202025-12-09%20at%2014.40.15.jpeg?access_token=f5442d04-6644-41c4-87dc-8072ad866e50\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33288,
                33289,
                33290,
                33291
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 272.73,
                                &quot;tax_amount&quot;: 272.73,
                                &quot;base_amount_currency&quot;: 2727.27,
                                &quot;base_amount&quot;: 2727.27,
                                &quot;display_base_amount_currency&quot;: 2727.27,
                                &quot;display_base_amount&quot;: 2727.27,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 272.73,
                        &quot;tax_amount&quot;: 272.73,
                        &quot;base_amount_currency&quot;: 2727.27,
                        &quot;base_amount&quot;: 2727.27,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 2727.27,
                &quot;base_amount&quot;: 2727.27,
                &quot;tax_amount_currency&quot;: 272.73,
                &quot;tax_amount&quot;: 272.73,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 3000,
                &quot;total_amount&quot;: 3000
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 21,
                    &quot;odoo_id&quot;: 33288,
                    &quot;sales_order_id&quot;: 5,
                    &quot;odoo_order_id&quot;: 12404,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;3000.00&quot;,
                    &quot;price_subtotal&quot;: &quot;2727.27&quot;,
                    &quot;price_total&quot;: &quot;3000.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 22,
                    &quot;odoo_id&quot;: 33289,
                    &quot;sales_order_id&quot;: 5,
                    &quot;odoo_order_id&quot;: 12404,
                    &quot;product_id&quot;: 1,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)\nBlack/Teal&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 23,
                    &quot;odoo_id&quot;: 33290,
                    &quot;sales_order_id&quot;: 5,
                    &quot;odoo_order_id&quot;: 12404,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 24,
                    &quot;odoo_id&quot;: 33291,
                    &quot;sales_order_id&quot;: 5,
                    &quot;odoo_order_id&quot;: 12404,
                    &quot;product_id&quot;: 32,
                    &quot;product_name&quot;: &quot;[3WM 7605S] 3 Way Mixer 7605S (U)&quot;,
                    &quot;name&quot;: &quot;[3WM 7605S] 3 Way Mixer 7605S (U)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 6,
            &quot;odoo_id&quot;: 12396,
            &quot;name&quot;: &quot;S12392&quot;,
            &quot;create_date&quot;: &quot;2025-12-09T07:56:02.000000Z&quot;,
            &quot;partner_id&quot;: 6414,
            &quot;partner_name&quot;: &quot;Phone Pyi Win&quot;,
            &quot;user_id&quot;: 367,
            &quot;user_name&quot;: &quot;LINKTRADE INTERNATIONAL PTY LTD (Kapila De Silva)&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Self Gen&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;S10059&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;4190.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;4190.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_2530.jpeg\&quot; src=\&quot;/web/image/289592-01d5395e/IMG_2530.jpeg?access_token=23e08cd9-b19d-40ef-9855-4c507b296597\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33239,
                33240
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 380.91,
                                &quot;tax_amount&quot;: 380.91,
                                &quot;base_amount_currency&quot;: 3809.09,
                                &quot;base_amount&quot;: 3809.09,
                                &quot;display_base_amount_currency&quot;: 3809.09,
                                &quot;display_base_amount&quot;: 3809.09,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 380.91,
                        &quot;tax_amount&quot;: 380.91,
                        &quot;base_amount_currency&quot;: 3809.09,
                        &quot;base_amount&quot;: 3809.09,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 3809.09,
                &quot;base_amount&quot;: 3809.09,
                &quot;tax_amount_currency&quot;: 380.91,
                &quot;tax_amount&quot;: 380.91,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 4190,
                &quot;total_amount&quot;: 4190
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 25,
                    &quot;odoo_id&quot;: 33239,
                    &quot;sales_order_id&quot;: 6,
                    &quot;odoo_order_id&quot;: 12396,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;4190.00&quot;,
                    &quot;price_subtotal&quot;: &quot;3809.09&quot;,
                    &quot;price_total&quot;: &quot;4190.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 26,
                    &quot;odoo_id&quot;: 33240,
                    &quot;sales_order_id&quot;: 6,
                    &quot;odoo_order_id&quot;: 12396,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 7,
            &quot;odoo_id&quot;: 12394,
            &quot;name&quot;: &quot;S12390&quot;,
            &quot;create_date&quot;: &quot;2025-12-09T07:41:09.000000Z&quot;,
            &quot;partner_id&quot;: 6413,
            &quot;partner_name&quot;: &quot;Jignesh Bhatt&quot;,
            &quot;user_id&quot;: 570,
            &quot;user_name&quot;: &quot;Noi Pukkaew&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Self Gen&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;S06349&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;3490.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;3490.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-09 at 1.15.55 PM.jpeg\&quot; src=\&quot;/web/image/289577-34418caa/WhatsApp%20Image%202025-12-09%20at%201.15.55%20PM.jpeg?access_token=4163ec41-1634-44c0-a6f7-96b379ad5020\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33236,
                33237
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 317.27,
                                &quot;tax_amount&quot;: 317.27,
                                &quot;base_amount_currency&quot;: 3172.73,
                                &quot;base_amount&quot;: 3172.73,
                                &quot;display_base_amount_currency&quot;: 3172.73,
                                &quot;display_base_amount&quot;: 3172.73,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 317.27,
                        &quot;tax_amount&quot;: 317.27,
                        &quot;base_amount_currency&quot;: 3172.73,
                        &quot;base_amount&quot;: 3172.73,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 3172.73,
                &quot;base_amount&quot;: 3172.73,
                &quot;tax_amount_currency&quot;: 317.27,
                &quot;tax_amount&quot;: 317.27,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 3490,
                &quot;total_amount&quot;: 3490
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 27,
                    &quot;odoo_id&quot;: 33236,
                    &quot;sales_order_id&quot;: 7,
                    &quot;odoo_order_id&quot;: 12394,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;3490.00&quot;,
                    &quot;price_subtotal&quot;: &quot;3172.73&quot;,
                    &quot;price_total&quot;: &quot;3490.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 28,
                    &quot;odoo_id&quot;: 33237,
                    &quot;sales_order_id&quot;: 7,
                    &quot;odoo_order_id&quot;: 12394,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 8,
            &quot;odoo_id&quot;: 12385,
            &quot;name&quot;: &quot;S12381&quot;,
            &quot;create_date&quot;: &quot;2025-12-09T03:30:36.000000Z&quot;,
            &quot;partner_id&quot;: 6412,
            &quot;partner_name&quot;: &quot;Brett Davis&quot;,
            &quot;user_id&quot;: 494,
            &quot;user_name&quot;: &quot;Brad Sumpter&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Self Gen&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Finance - Brighte&quot;,
            &quot;amount_total&quot;: &quot;5590.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;5590.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33225,
                33226,
                33227
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 508.18,
                                &quot;tax_amount&quot;: 508.18,
                                &quot;base_amount_currency&quot;: 5081.82,
                                &quot;base_amount&quot;: 5081.82,
                                &quot;display_base_amount_currency&quot;: 5081.82,
                                &quot;display_base_amount&quot;: 5081.82,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 508.18,
                        &quot;tax_amount&quot;: 508.18,
                        &quot;base_amount_currency&quot;: 5081.82,
                        &quot;base_amount&quot;: 5081.82,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 5081.82,
                &quot;base_amount&quot;: 5081.82,
                &quot;tax_amount_currency&quot;: 508.18,
                &quot;tax_amount&quot;: 508.18,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 5590,
                &quot;total_amount&quot;: 5590
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 32,
                    &quot;odoo_id&quot;: 33225,
                    &quot;sales_order_id&quot;: 8,
                    &quot;odoo_order_id&quot;: 12385,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;5590.00&quot;,
                    &quot;price_subtotal&quot;: &quot;5081.82&quot;,
                    &quot;price_total&quot;: &quot;5590.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 33,
                    &quot;odoo_id&quot;: 33226,
                    &quot;sales_order_id&quot;: 8,
                    &quot;odoo_order_id&quot;: 12385,
                    &quot;product_id&quot;: 56,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 34,
                    &quot;odoo_id&quot;: 33227,
                    &quot;sales_order_id&quot;: 8,
                    &quot;odoo_order_id&quot;: 12385,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 9,
            &quot;odoo_id&quot;: 12384,
            &quot;name&quot;: &quot;S12380&quot;,
            &quot;create_date&quot;: &quot;2025-12-09T02:33:24.000000Z&quot;,
            &quot;partner_id&quot;: 5997,
            &quot;partner_name&quot;: &quot;Amie Renton-Williams&quot;,
            &quot;user_id&quot;: 504,
            &quot;user_name&quot;: &quot;Eric Park&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;800.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;0.00&quot;,
            &quot;delivery_status&quot;: &quot;full&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;not_paid&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p&gt;Supply Only - Qty: 1.0&lt;br&gt;WAA Benchtop 5 in 1 Hydrogen - Qty: 1.0&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33223,
                33224
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 72.73,
                                &quot;tax_amount&quot;: 72.73,
                                &quot;base_amount_currency&quot;: 727.27,
                                &quot;base_amount&quot;: 727.27,
                                &quot;display_base_amount_currency&quot;: 727.27,
                                &quot;display_base_amount&quot;: 727.27,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 72.73,
                        &quot;tax_amount&quot;: 72.73,
                        &quot;base_amount_currency&quot;: 727.27,
                        &quot;base_amount&quot;: 727.27,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 727.27,
                &quot;base_amount&quot;: 727.27,
                &quot;tax_amount_currency&quot;: 72.73,
                &quot;tax_amount&quot;: 72.73,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 800,
                &quot;total_amount&quot;: 800
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 35,
                    &quot;odoo_id&quot;: 33223,
                    &quot;sales_order_id&quot;: 9,
                    &quot;odoo_order_id&quot;: 12384,
                    &quot;product_id&quot;: 46,
                    &quot;product_name&quot;: &quot;Supply Only&quot;,
                    &quot;name&quot;: &quot;Supply Only&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;800.00&quot;,
                    &quot;price_subtotal&quot;: &quot;727.27&quot;,
                    &quot;price_total&quot;: &quot;800.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 36,
                    &quot;odoo_id&quot;: 33224,
                    &quot;sales_order_id&quot;: 9,
                    &quot;odoo_order_id&quot;: 12384,
                    &quot;product_id&quot;: 66,
                    &quot;product_name&quot;: &quot;[BTRO-5IN1-G1IHCH] WAA Benchtop 5 in 1 Hydrogen&quot;,
                    &quot;name&quot;: &quot;[BTRO-5IN1-G1IHCH] WAA Benchtop 5 in 1 Hydrogen&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 10,
            &quot;odoo_id&quot;: 12380,
            &quot;name&quot;: &quot;S12376&quot;,
            &quot;create_date&quot;: &quot;2025-12-09T01:33:56.000000Z&quot;,
            &quot;partner_id&quot;: 6410,
            &quot;partner_name&quot;: &quot;Harmanjit Singh Koonar&quot;,
            &quot;user_id&quot;: 573,
            &quot;user_name&quot;: &quot;Jean-Claude Guillemain&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Finance - Brighte&quot;,
            &quot;amount_total&quot;: &quot;5500.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;5500.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-Black (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7624G (U Detachable) - Qty: 1.0&lt;br&gt;Stand Alone Back Metal Panel (Black) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-08 at 8.27.06 AM (1).jpeg\&quot; src=\&quot;/web/image/289110-ffe4af85/WhatsApp%20Image%202025-12-08%20at%208.27.06%20AM%20%281%29.jpeg?access_token=0cfb38f7-e9eb-43c6-a1e2-b59fd1f6d53a\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-08 at 8.27.06 AM.jpeg\&quot; src=\&quot;/web/image/289111-1dd163f9/WhatsApp%20Image%202025-12-08%20at%208.27.06%20AM.jpeg?access_token=6112cb3f-c9f9-44d3-bb22-b2b8234da954\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;WhatsApp Image 2025-12-08 at 8.27.38 AM.jpeg\&quot; src=\&quot;/web/image/289112-3d1b1c12/WhatsApp%20Image%202025-12-08%20at%208.27.38%20AM.jpeg?access_token=4e57ba40-f205-4cdc-a9e7-549de5199ae9\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33206,
                33207,
                33208,
                33209,
                33210
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 500,
                                &quot;tax_amount&quot;: 500,
                                &quot;base_amount_currency&quot;: 5000,
                                &quot;base_amount&quot;: 5000,
                                &quot;display_base_amount_currency&quot;: 5000,
                                &quot;display_base_amount&quot;: 5000,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 500,
                        &quot;tax_amount&quot;: 500,
                        &quot;base_amount_currency&quot;: 5000,
                        &quot;base_amount&quot;: 5000,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 5000,
                &quot;base_amount&quot;: 5000,
                &quot;tax_amount_currency&quot;: 500,
                &quot;tax_amount&quot;: 500,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 5500,
                &quot;total_amount&quot;: 5500
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 37,
                    &quot;odoo_id&quot;: 33206,
                    &quot;sales_order_id&quot;: 10,
                    &quot;odoo_order_id&quot;: 12380,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;5500.00&quot;,
                    &quot;price_subtotal&quot;: &quot;5000.00&quot;,
                    &quot;price_total&quot;: &quot;5500.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 38,
                    &quot;odoo_id&quot;: 33207,
                    &quot;sales_order_id&quot;: 10,
                    &quot;odoo_order_id&quot;: 12380,
                    &quot;product_id&quot;: 1,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)\nBlack/Teal&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 39,
                    &quot;odoo_id&quot;: 33208,
                    &quot;sales_order_id&quot;: 10,
                    &quot;odoo_order_id&quot;: 12380,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 40,
                    &quot;odoo_id&quot;: 33209,
                    &quot;sales_order_id&quot;: 10,
                    &quot;odoo_order_id&quot;: 12380,
                    &quot;product_id&quot;: 37,
                    &quot;product_name&quot;: &quot;[3WM 7624G] 3 Way Mixer 7624G (U Detachable)&quot;,
                    &quot;name&quot;: &quot;[3WM 7624G] 3 Way Mixer 7624G (U Detachable)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 41,
                    &quot;odoo_id&quot;: 33210,
                    &quot;sales_order_id&quot;: 10,
                    &quot;odoo_order_id&quot;: 12380,
                    &quot;product_id&quot;: 9,
                    &quot;product_name&quot;: &quot;Stand Alone Back Metal Panel (Black)&quot;,
                    &quot;name&quot;: &quot;Stand Alone Back Metal Panel (Black)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 11,
            &quot;odoo_id&quot;: 12377,
            &quot;name&quot;: &quot;S12373&quot;,
            &quot;create_date&quot;: &quot;2025-12-09T00:52:57.000000Z&quot;,
            &quot;partner_id&quot;: 6409,
            &quot;partner_name&quot;: &quot;Stephen Ellis&quot;,
            &quot;user_id&quot;: 367,
            &quot;user_name&quot;: &quot;LINKTRADE INTERNATIONAL PTY LTD (Kapila De Silva)&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;0&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;5090.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;5090.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-Black (w filters) - Qty: 1.0&lt;br&gt;WAA Benchtop 5 in 1 Hydrogen - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;IMG_4499.jpg\&quot; src=\&quot;/web/image/289032-bdc9ad65/IMG_4499.jpg?access_token=1a33cd4d-9d16-41dd-a161-a8322e0368ab\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33186,
                33187,
                33188
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 462.73,
                                &quot;tax_amount&quot;: 462.73,
                                &quot;base_amount_currency&quot;: 4627.27,
                                &quot;base_amount&quot;: 4627.27,
                                &quot;display_base_amount_currency&quot;: 4627.27,
                                &quot;display_base_amount&quot;: 4627.27,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 462.73,
                        &quot;tax_amount&quot;: 462.73,
                        &quot;base_amount_currency&quot;: 4627.27,
                        &quot;base_amount&quot;: 4627.27,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 4627.27,
                &quot;base_amount&quot;: 4627.27,
                &quot;tax_amount_currency&quot;: 462.73,
                &quot;tax_amount&quot;: 462.73,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 5090,
                &quot;total_amount&quot;: 5090
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 42,
                    &quot;odoo_id&quot;: 33186,
                    &quot;sales_order_id&quot;: 11,
                    &quot;odoo_order_id&quot;: 12377,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;5090.00&quot;,
                    &quot;price_subtotal&quot;: &quot;4627.27&quot;,
                    &quot;price_total&quot;: &quot;5090.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 43,
                    &quot;odoo_id&quot;: 33187,
                    &quot;sales_order_id&quot;: 11,
                    &quot;odoo_order_id&quot;: 12377,
                    &quot;product_id&quot;: 1,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)\nBlack/Teal&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 44,
                    &quot;odoo_id&quot;: 33188,
                    &quot;sales_order_id&quot;: 11,
                    &quot;odoo_order_id&quot;: 12377,
                    &quot;product_id&quot;: 66,
                    &quot;product_name&quot;: &quot;[BTRO-5IN1-G1IHCH] WAA Benchtop 5 in 1 Hydrogen&quot;,
                    &quot;name&quot;: &quot;[BTRO-5IN1-G1IHCH] WAA Benchtop 5 in 1 Hydrogen&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 12,
            &quot;odoo_id&quot;: 12374,
            &quot;name&quot;: &quot;S12370&quot;,
            &quot;create_date&quot;: &quot;2025-12-09T00:29:41.000000Z&quot;,
            &quot;partner_id&quot;: 6407,
            &quot;partner_name&quot;: &quot;Basil Almehdawy&quot;,
            &quot;user_id&quot;: 572,
            &quot;user_name&quot;: &quot;Bronwyn Parnell&quot;,
            &quot;team_id&quot;: 1,
            &quot;team_name&quot;: &quot;Sales&quot;,
            &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
            &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
            &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
            &quot;x_studio_referred_by&quot;: &quot;S10563&quot;,
            &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
            &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
            &quot;amount_total&quot;: &quot;4900.00&quot;,
            &quot;amount_to_invoice&quot;: &quot;4900.00&quot;,
            &quot;delivery_status&quot;: &quot;pending&quot;,
            &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
            &quot;state&quot;: &quot;sale&quot;,
            &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-Black (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7624S (U Detachable) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;1.jpg\&quot; src=\&quot;/web/image/289003-474ff91f/1.jpg?access_token=6ec3c573-08b9-4651-a8b0-8c32f8ed5b5a\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;2.jpg\&quot; src=\&quot;/web/image/289004-dd942e12/2.jpg?access_token=30bbbb66-5f0d-4b7c-a813-82631c5c2827\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;3.jpg\&quot; src=\&quot;/web/image/289005-a3e96d85/3.jpg?access_token=ea9e8036-727e-472e-b754-d86e6eef97fe\&quot;&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;PHOTO-2025-12-09-11-30-57.jpg\&quot; src=\&quot;/web/image/289121-5f612386/PHOTO-2025-12-09-11-30-57.jpg?access_token=b65f9c02-5c96-4ae2-8bfc-d752a955786a\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;PHOTO-2025-12-09-11-32-51.jpg\&quot; src=\&quot;/web/image/289119-751d1232/PHOTO-2025-12-09-11-32-51.jpg?access_token=b4a2ae12-6763-471c-9077-8c43ebdcc296\&quot;&gt;&lt;/p&gt;&quot;,
            &quot;tag_ids&quot;: [
                2
            ],
            &quot;order_line&quot;: [
                33173,
                33174,
                33175,
                33176
            ],
            &quot;tax_totals&quot;: {
                &quot;currency_id&quot;: 20,
                &quot;currency_pd&quot;: 0.01,
                &quot;company_currency_id&quot;: 20,
                &quot;company_currency_pd&quot;: 0.01,
                &quot;has_tax_groups&quot;: true,
                &quot;subtotals&quot;: [
                    {
                        &quot;tax_groups&quot;: [
                            {
                                &quot;id&quot;: 3,
                                &quot;involved_tax_ids&quot;: [
                                    5
                                ],
                                &quot;tax_amount_currency&quot;: 445.45,
                                &quot;tax_amount&quot;: 445.45,
                                &quot;base_amount_currency&quot;: 4454.55,
                                &quot;base_amount&quot;: 4454.55,
                                &quot;display_base_amount_currency&quot;: 4454.55,
                                &quot;display_base_amount&quot;: 4454.55,
                                &quot;group_name&quot;: &quot;GST 10%&quot;,
                                &quot;group_label&quot;: false
                            }
                        ],
                        &quot;tax_amount_currency&quot;: 445.45,
                        &quot;tax_amount&quot;: 445.45,
                        &quot;base_amount_currency&quot;: 4454.55,
                        &quot;base_amount&quot;: 4454.55,
                        &quot;name&quot;: &quot;Untaxed Amount&quot;
                    }
                ],
                &quot;base_amount_currency&quot;: 4454.55,
                &quot;base_amount&quot;: 4454.55,
                &quot;tax_amount_currency&quot;: 445.45,
                &quot;tax_amount&quot;: 445.45,
                &quot;same_tax_base&quot;: true,
                &quot;total_amount_currency&quot;: 4900,
                &quot;total_amount&quot;: 4900
            },
            &quot;created_at&quot;: &quot;2025-12-10T02:11:41.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
            &quot;lines&quot;: [
                {
                    &quot;id&quot;: 45,
                    &quot;odoo_id&quot;: 33173,
                    &quot;sales_order_id&quot;: 12,
                    &quot;odoo_order_id&quot;: 12374,
                    &quot;product_id&quot;: 10,
                    &quot;product_name&quot;: &quot;Installation Service&quot;,
                    &quot;name&quot;: &quot;Installation Service&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;4900.00&quot;,
                    &quot;price_subtotal&quot;: &quot;4454.55&quot;,
                    &quot;price_total&quot;: &quot;4900.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 46,
                    &quot;odoo_id&quot;: 33174,
                    &quot;sales_order_id&quot;: 12,
                    &quot;odoo_order_id&quot;: 12374,
                    &quot;product_id&quot;: 1,
                    &quot;product_name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)&quot;,
                    &quot;name&quot;: &quot;[FHWR-3S1-20-B] WAA Full House Healthy Water POE 3 V2-Black (w filters)\nBlack/Teal&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 47,
                    &quot;odoo_id&quot;: 33175,
                    &quot;sales_order_id&quot;: 12,
                    &quot;odoo_order_id&quot;: 12374,
                    &quot;product_id&quot;: 2,
                    &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
                    &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                },
                {
                    &quot;id&quot;: 48,
                    &quot;odoo_id&quot;: 33176,
                    &quot;sales_order_id&quot;: 12,
                    &quot;odoo_order_id&quot;: 12374,
                    &quot;product_id&quot;: 38,
                    &quot;product_name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
                    &quot;name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
                    &quot;product_uom_qty&quot;: &quot;1.00&quot;,
                    &quot;price_unit&quot;: &quot;0.00&quot;,
                    &quot;price_subtotal&quot;: &quot;0.00&quot;,
                    &quot;price_total&quot;: &quot;0.00&quot;,
                    &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
                }
            ]
        }
    ],
    &quot;first_page_url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=1&quot;,
    &quot;from&quot;: 1,
    &quot;last_page&quot;: 324,
    &quot;last_page_url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=324&quot;,
    &quot;links&quot;: [
        {
            &quot;url&quot;: null,
            &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
            &quot;page&quot;: null,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=1&quot;,
            &quot;label&quot;: &quot;1&quot;,
            &quot;page&quot;: 1,
            &quot;active&quot;: true
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=2&quot;,
            &quot;label&quot;: &quot;2&quot;,
            &quot;page&quot;: 2,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=3&quot;,
            &quot;label&quot;: &quot;3&quot;,
            &quot;page&quot;: 3,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=4&quot;,
            &quot;label&quot;: &quot;4&quot;,
            &quot;page&quot;: 4,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=5&quot;,
            &quot;label&quot;: &quot;5&quot;,
            &quot;page&quot;: 5,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=6&quot;,
            &quot;label&quot;: &quot;6&quot;,
            &quot;page&quot;: 6,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=7&quot;,
            &quot;label&quot;: &quot;7&quot;,
            &quot;page&quot;: 7,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=8&quot;,
            &quot;label&quot;: &quot;8&quot;,
            &quot;page&quot;: 8,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=9&quot;,
            &quot;label&quot;: &quot;9&quot;,
            &quot;page&quot;: 9,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=10&quot;,
            &quot;label&quot;: &quot;10&quot;,
            &quot;page&quot;: 10,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: null,
            &quot;label&quot;: &quot;...&quot;,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=323&quot;,
            &quot;label&quot;: &quot;323&quot;,
            &quot;page&quot;: 323,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=324&quot;,
            &quot;label&quot;: &quot;324&quot;,
            &quot;page&quot;: 324,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=2&quot;,
            &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
            &quot;page&quot;: 2,
            &quot;active&quot;: false
        }
    ],
    &quot;next_page_url&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders?page=2&quot;,
    &quot;path&quot;: &quot;http://wateranalytics-australia-api.test/api/sales-orders&quot;,
    &quot;per_page&quot;: 15,
    &quot;prev_page_url&quot;: null,
    &quot;to&quot;: 15,
    &quot;total&quot;: 4860
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-sales-orders" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-sales-orders"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-sales-orders"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-sales-orders" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-sales-orders">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-sales-orders" data-method="GET"
      data-path="api/sales-orders"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-sales-orders', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-sales-orders"
                    onclick="tryItOut('GETapi-sales-orders');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-sales-orders"
                    onclick="cancelTryOut('GETapi-sales-orders');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-sales-orders"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/sales-orders</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-sales-orders"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-sales-orders"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page"                data-endpoint="GETapi-sales-orders"
               value="1"
               data-component="query">
    <br>
<p>The page number. Example: <code>1</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>per_page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="per_page"                data-endpoint="GETapi-sales-orders"
               value="15"
               data-component="query">
    <br>
<p>The number of items per page. Example: <code>15</code></p>
            </div>
                </form>

                    <h2 id="sales-orders-GETapi-sales-orders--id-">Get Sales Order</h2>

<p>
</p>

<p>Get a specific sales order by its ID (local ID) or Odoo ID.</p>

<span id="example-requests-GETapi-sales-orders--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://wateranalytics-australia-api.test/api/sales-orders/1" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://wateranalytics-australia-api.test/api/sales-orders/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-sales-orders--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 1,
    &quot;odoo_id&quot;: 12409,
    &quot;name&quot;: &quot;S12405&quot;,
    &quot;create_date&quot;: &quot;2025-12-10T01:29:13.000000Z&quot;,
    &quot;partner_id&quot;: 6419,
    &quot;partner_name&quot;: &quot;Yul Brisset&quot;,
    &quot;user_id&quot;: 367,
    &quot;user_name&quot;: &quot;LINKTRADE INTERNATIONAL PTY LTD (Kapila De Silva)&quot;,
    &quot;team_id&quot;: 1,
    &quot;team_name&quot;: &quot;Sales&quot;,
    &quot;x_studio_sales_rep_1&quot;: &quot;0&quot;,
    &quot;x_studio_sales_source&quot;: &quot;Company Lead&quot;,
    &quot;x_studio_commission_paid&quot;: &quot;0&quot;,
    &quot;x_studio_referred_by&quot;: &quot;0&quot;,
    &quot;x_studio_referrer_processed&quot;: &quot;0&quot;,
    &quot;x_studio_payment_type&quot;: &quot;Cash or Online Payment&quot;,
    &quot;amount_total&quot;: &quot;4890.00&quot;,
    &quot;amount_to_invoice&quot;: &quot;4890.00&quot;,
    &quot;delivery_status&quot;: &quot;pending&quot;,
    &quot;x_studio_invoice_payment_status&quot;: &quot;0&quot;,
    &quot;state&quot;: &quot;sale&quot;,
    &quot;internal_note_display&quot;: &quot;&lt;p data-oe-version=\&quot;1.2\&quot;&gt;Installation Service - Qty: 1.0&lt;br&gt;WAA Full House Healthy Water POE 3 V2-White (w filters) - Qty: 1.0&lt;br&gt;WAA Healthy U/S RO POU 3 (w filters) - Qty: 1.0&lt;br&gt;3 Way Mixer 7624S (U Detachable) - Qty: 1.0&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;image000000.JPG\&quot; src=\&quot;/web/image/290000-383944d8/image000000.JPG?access_token=45657b10-2f17-4b15-beaa-df41fa1295c0\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;image000001.JPG\&quot; src=\&quot;/web/image/290001-4eb2d2f5/image000001.JPG?access_token=6443b67d-909d-4df0-8e79-94386344ff38\&quot;&gt;&lt;img class=\&quot;img-fluid\&quot; data-file-name=\&quot;image000002.JPG\&quot; src=\&quot;/web/image/290002-57645e82/image000002.JPG?access_token=58443aa2-0410-4fd1-8520-d09af32fac09\&quot;&gt;&lt;/p&gt;&quot;,
    &quot;tag_ids&quot;: [
        2
    ],
    &quot;order_line&quot;: [
        33305,
        33306,
        33307,
        33308
    ],
    &quot;tax_totals&quot;: {
        &quot;currency_id&quot;: 20,
        &quot;currency_pd&quot;: 0.01,
        &quot;company_currency_id&quot;: 20,
        &quot;company_currency_pd&quot;: 0.01,
        &quot;has_tax_groups&quot;: true,
        &quot;subtotals&quot;: [
            {
                &quot;tax_groups&quot;: [
                    {
                        &quot;id&quot;: 3,
                        &quot;involved_tax_ids&quot;: [
                            5
                        ],
                        &quot;tax_amount_currency&quot;: 444.55,
                        &quot;tax_amount&quot;: 444.55,
                        &quot;base_amount_currency&quot;: 4445.45,
                        &quot;base_amount&quot;: 4445.45,
                        &quot;display_base_amount_currency&quot;: 4445.45,
                        &quot;display_base_amount&quot;: 4445.45,
                        &quot;group_name&quot;: &quot;GST 10%&quot;,
                        &quot;group_label&quot;: false
                    }
                ],
                &quot;tax_amount_currency&quot;: 444.55,
                &quot;tax_amount&quot;: 444.55,
                &quot;base_amount_currency&quot;: 4445.45,
                &quot;base_amount&quot;: 4445.45,
                &quot;name&quot;: &quot;Untaxed Amount&quot;
            }
        ],
        &quot;base_amount_currency&quot;: 4445.45,
        &quot;base_amount&quot;: 4445.45,
        &quot;tax_amount_currency&quot;: 444.55,
        &quot;tax_amount&quot;: 444.55,
        &quot;same_tax_base&quot;: true,
        &quot;total_amount_currency&quot;: 4890,
        &quot;total_amount&quot;: 4890
    },
    &quot;created_at&quot;: &quot;2025-12-10T02:09:55.000000Z&quot;,
    &quot;updated_at&quot;: &quot;2025-12-10T02:48:28.000000Z&quot;,
    &quot;lines&quot;: [
        {
            &quot;id&quot;: 7,
            &quot;odoo_id&quot;: 33305,
            &quot;sales_order_id&quot;: 1,
            &quot;odoo_order_id&quot;: 12409,
            &quot;product_id&quot;: 10,
            &quot;product_name&quot;: &quot;Installation Service&quot;,
            &quot;name&quot;: &quot;Installation Service&quot;,
            &quot;product_uom_qty&quot;: &quot;1.00&quot;,
            &quot;price_unit&quot;: &quot;4890.00&quot;,
            &quot;price_subtotal&quot;: &quot;4445.45&quot;,
            &quot;price_total&quot;: &quot;4890.00&quot;,
            &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
        },
        {
            &quot;id&quot;: 8,
            &quot;odoo_id&quot;: 33306,
            &quot;sales_order_id&quot;: 1,
            &quot;odoo_order_id&quot;: 12409,
            &quot;product_id&quot;: 56,
            &quot;product_name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)&quot;,
            &quot;name&quot;: &quot;[FHWR-3S1-20-W] WAA Full House Healthy Water POE 3 V2-White (w filters)\nWhite/Grey&quot;,
            &quot;product_uom_qty&quot;: &quot;1.00&quot;,
            &quot;price_unit&quot;: &quot;0.00&quot;,
            &quot;price_subtotal&quot;: &quot;0.00&quot;,
            &quot;price_total&quot;: &quot;0.00&quot;,
            &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
        },
        {
            &quot;id&quot;: 9,
            &quot;odoo_id&quot;: 33307,
            &quot;sales_order_id&quot;: 1,
            &quot;odoo_order_id&quot;: 12409,
            &quot;product_id&quot;: 2,
            &quot;product_name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)&quot;,
            &quot;name&quot;: &quot;[USRO-3S1-2W] WAA Healthy U/S RO POU 3 (w filters)\nUSRO-3S1-2W&quot;,
            &quot;product_uom_qty&quot;: &quot;1.00&quot;,
            &quot;price_unit&quot;: &quot;0.00&quot;,
            &quot;price_subtotal&quot;: &quot;0.00&quot;,
            &quot;price_total&quot;: &quot;0.00&quot;,
            &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
        },
        {
            &quot;id&quot;: 10,
            &quot;odoo_id&quot;: 33308,
            &quot;sales_order_id&quot;: 1,
            &quot;odoo_order_id&quot;: 12409,
            &quot;product_id&quot;: 38,
            &quot;product_name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
            &quot;name&quot;: &quot;[3WM 7624S] 3 Way Mixer 7624S (U Detachable)&quot;,
            &quot;product_uom_qty&quot;: &quot;1.00&quot;,
            &quot;price_unit&quot;: &quot;0.00&quot;,
            &quot;price_subtotal&quot;: &quot;0.00&quot;,
            &quot;price_total&quot;: &quot;0.00&quot;,
            &quot;created_at&quot;: &quot;2025-12-10T02:40:23.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2025-12-10T02:48:30.000000Z&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-sales-orders--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-sales-orders--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-sales-orders--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-sales-orders--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-sales-orders--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-sales-orders--id-" data-method="GET"
      data-path="api/sales-orders/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-sales-orders--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-sales-orders--id-"
                    onclick="tryItOut('GETapi-sales-orders--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-sales-orders--id-"
                    onclick="cancelTryOut('GETapi-sales-orders--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-sales-orders--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/sales-orders/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-sales-orders--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-sales-orders--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-sales-orders--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the sales order. Example: <code>1</code></p>
            </div>
                    </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
