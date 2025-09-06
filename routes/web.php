<?php

use App\Http\Controllers\InvoiceAccountingListController;
use App\Http\Controllers\InvoiceArticlesController;
use App\Http\Controllers\InvoiceCategoryController;
use App\Http\Controllers\InvoiceCompanyController;
use App\Http\Controllers\InvoiceLocationController;
use App\Http\Controllers\InvoiceSatController;
use App\Http\Controllers\InvoiceTermController;
use App\Http\Controllers\PlantaController;
use App\Models\InvoiceCompany;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Aws\S3\S3Client;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $usersCount = User::count();
        return Inertia::render('Dashboard', 
            ['users' => $usersCount]
        ); // Inertia busca en 'resources/js/pages/Dashboard.vue'
    })->name('/dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/plantas', [PlantaController::class, 'byUser'])->name('plantas');

    Route::get('/listas-contabilidad', [InvoiceAccountingListController::class, 'index'])->name('/listas-contabilidad');
    Route::post('/listas-contabilidad', [InvoiceAccountingListController::class, 'store'])->name('listas-contabilidad.store');
    Route::delete('/listas-contabilidad/{invoiceAccountingList}', [InvoiceAccountingListController::class, 'destroy'])->name('listas-contabilidad.destroy');
    Route::put('/listas-contabilidad/{invoiceAccountingList}', [InvoiceAccountingListController::class, 'update'])->name('listas-contabilidad.update');

    Route::get('/articulos', [InvoiceArticlesController::class, 'index'])->name('/articulos');
    Route::post('/articulos', [InvoiceArticlesController::class, 'store'])->name('articulos.store');
    Route::delete('/articulos/{invoiceArticles}', [InvoiceArticlesController::class, 'destroy'])->name('articulos.destroy');
    Route::put('/articulos/{invoiceArticles}', [InvoiceArticlesController::class, 'update'])->name('articulos.update');

    Route::get('/categoria-facturas', [InvoiceCategoryController::class, 'index'])->name('/categoria-facturas');
    Route::post('/categoria-facturas', [InvoiceCategoryController::class, 'store'])->name('categoria-facturas.store');
    Route::delete('/categoria-facturas/{invoiceCategory}', [InvoiceCategoryController::class, 'destroy'])->name('categoria-facturas.destroy');
    Route::put('/categoria-facturas/{invoiceCategory}', [InvoiceCategoryController::class, 'update'])->name('categoria-facturas.update');
    Route::post('/categoria-facturas/delete-multiple', [InvoiceCategoryController::class, 'deleteMultiple'])->name('categoria-facturas.delete-multiple');

    Route::get('/clases-facturacion', [App\Http\Controllers\InvoiceClassController::class, 'index'])->name('/clases-facturacion');
    Route::post('/clases-facturacion', [App\Http\Controllers\InvoiceClassController::class, 'store'])->name('clases-facturacion.store');
    Route::delete('/clases-facturacion/{invoiceClass}', [App\Http\Controllers\InvoiceClassController::class, 'destroy'])->name('clases-facturacion.destroy');
    Route::put('/clases-facturacion/{invoiceClass}', [App\Http\Controllers\InvoiceClassController::class, 'update'])->name('clases-facturacion.update');
    Route::post('/clases-facturacion/delete-multiple', [App\Http\Controllers\InvoiceClassController::class, 'deleteMultiple'])->name('clases-facturacion.delete-multiple');

    Route::get('/empresas', [InvoiceCompanyController::class, 'index'])->name('/empresas');
    Route::post('/empresas', [InvoiceCompanyController::class, 'store'])->name('empresas.store');
    Route::delete('/empresas/{invoiceCompany}', [InvoiceCompanyController::class, 'destroy'])->name('empresas.destroy');
    Route::put('/empresas/{invoiceCompany}', [InvoiceCompanyController::class, 'update'])->name('empresas.update');

    Route::get('/ubicaciones', [InvoiceLocationController::class, 'index'])->name('/ubicaciones');
    Route::post('/ubicaciones', [InvoiceLocationController::class, 'store'])->name('ubicaciones.store');
    Route::delete('/ubicaciones/{invoiceLocation}', [InvoiceLocationController::class,'destroy'])->name('ubicaciones.destroy');
    Route::put('/ubicaciones/{invoiceLocation}', [InvoiceLocationController::class,'update'])->name('ubicaciones.update');

    Route::get('/terminos-pago', [InvoiceTermController::class, 'index'])->name('/terminos-pago');
    Route::post('/terminos-pago', [InvoiceTermController::class, 'store'])->name('terminos-pago.store');
    Route::delete('/terminos-pago/{invoiceTerm}', [InvoiceTermController::class,'destroy'])->name('terminos-pago.destroy');
    Route::put('/terminos-pago/{invoiceTerm}', [InvoiceTermController::class,'update'])->name('terminos-pago.update');

    Route::get('/invoices/{invoiceSat}', [InvoiceSatController::class, 'sendNetsuite'])->name('invoices.send-netsuite');
});


Route::get('/xml-table', [App\Http\Controllers\InvoiceSatController::class, 'index'])->name('/xml-table');


Route::get('/spaces/test-download', function (Request $request) {
    $path = $request->query('path');
    abort_if(empty($path), 400, 'Falta ?path=');

    $s3 = new S3Client([
        'version' => 'latest',
        'region' => env('SPACES_REGION', 'sfo3'),
        'endpoint' => env('SPACES_ENDPOINT', 'https://sfo3.digitaloceanspaces.com'),
        'credentials' => [
            'key' => env('SPACES_KEY'),
            'secret' => env('SPACES_SECRET'),
        ],
        'use_path_style_endpoint' => false,
    ]);

    $cmd = $s3->getCommand('GetObject', [
        'Bucket' => env('SPACES_BUCKET'),
        'Key'    => $path,
        'ResponseContentDisposition' => 'attachment; filename="'.basename($path).'"',
    ]);
    $req = $s3->createPresignedRequest($cmd, '+5 minutes');
    $url = (string) $req->getUri();

    return redirect()->away($url);
});



//Route::get('/plantas', [PlantaController::class, 'byUser'])->name('plantas');
//Route::get('/planta/usuario', [EmpleadoController::class, 'getPlantaEmpleado'])->name('planta.usuario');

