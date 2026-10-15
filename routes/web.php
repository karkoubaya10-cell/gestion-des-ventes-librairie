<?php

use App\Http\Controllers\SalesController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/connexion', [LoginController::class, 'create'])->name('login');
Route::post('/connexion', [LoginController::class, 'store'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', [SalesController::class, 'index'])->name('sales.index');
    Route::middleware('admin')->group(function () {
        Route::post('/produits', [SalesController::class, 'storeCatalogProduct'])->name('catalog.products.store');
        Route::put('/produits/{product}', [SalesController::class, 'updateCatalogProduct'])->name('catalog.products.update');
        Route::delete('/produits/{product}', [SalesController::class, 'deleteCatalogProduct'])->name('catalog.products.delete');
        Route::post('/panier/produit', [SalesController::class, 'storeProduct'])->name('products.store');
    });
    Route::post('/panier/scan', [SalesController::class, 'scan'])->name('cart.scan');
    Route::post('/panier/modifier', [SalesController::class, 'updateCart'])->name('cart.update');
    Route::post('/panier/vider', [SalesController::class, 'clearCart'])->name('cart.clear');
    Route::post('/ventes', [SalesController::class, 'checkout'])->name('sales.checkout');
    Route::get('/ventes/{vente}', [SalesController::class, 'show'])->name('sales.show');
});
