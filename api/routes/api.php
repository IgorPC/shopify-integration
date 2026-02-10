<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request) {
    $test = new \App\Http\Services\ShopifyService();
    $test2 = app(\App\Http\Services\ProductService::class);

//    dd($test2->updateProductById('gid://shopify/Product/10041553518912', 'Bola Mikasa', '<p>Amazing volleyball</p>', 300));
//    dd($test2->createProduct('Bola 6.0', 230.23, '<p>It is another amazing ball</p>', 25));
//    dd($test2->deleteProductById('gid://shopify/Product/10041582158144'));
//    dd($test2->getAllProductsPaginated());
//    dd($test2->getProductById('gid://shopify/Product/10041585074496'));
//    dd($test2->syncLocalProductToShopify('gid://shopify/Product/10041585074496'));
//    dd($test2->bulkSyncLocalProductToShopify(['gid://shopify/Product/10041580912960']));
//    dd($test2->syncAll());
});
