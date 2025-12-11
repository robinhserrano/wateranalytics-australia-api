<?php

use Illuminate\Support\Facades\Route;
use Obuchmann\OdooJsonRpc\Odoo;

Route::get('/debug-odoo', function (Odoo $odoo) {
    dd(get_class_methods($odoo));
});
