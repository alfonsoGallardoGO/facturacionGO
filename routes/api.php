<?php

use App\Http\Controllers\InvoiceSatController;
use App\Http\Controllers\SpacesFolderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/invoices/{invoiceSat}', [InvoiceSatController::class, 'sendNetsuite']);

Route::get('/sat-download', [InvoiceSatController::class, 'descargaSat']);
Route::get('/sat/verify/{requestId}', [InvoiceSatController::class, 'verificarSat']);
