<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/catalog', [ProductController::class, 'index'])->name('catalog.index');
Route::post('/create-order', [OrderController::class, 'create'])->name('catalog.create');
Route::post('/approve-order', [OrderController::class, 'approve'])->name('order.approve');