<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WooCommerceWebhookController;


Route::get('/', [HomeController::class, 'index']);
Route::get('/product/{id}', [ProductController::class,'show'])->name('products.show');


Route::post('/webhook/orders', [WooCommerceWebhookController::class, 'handleOrder']);
Route::post('/webhook/products', [WooCommerceWebhookController::class, 'handleProduct']);
Route::post('/webhook/customers', [WooCommerceWebhookController::class, 'handleCustomer']);



