<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   return response()->json([
        'project' => 'Shopify Integration API',
        'version' => '1.0.0',
        'php_version' => PHP_VERSION,
        'framework' => 'Laravel 12',
        'graphql_endpoint' => url('/graphql'),
        'status' => 'Running'
    ]);
});
