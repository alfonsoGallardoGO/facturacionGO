<?php

use App\Http\Controllers\PlantaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Aws\S3\S3Client;

Route::get('/', function () {
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
        return Inertia::render('Dashboard'); // Inertia busca en 'resources/js/pages/Dashboard.vue'
    })->name('/dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/plantas', [PlantaController::class, 'byUser'])->name('plantas');
});

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

