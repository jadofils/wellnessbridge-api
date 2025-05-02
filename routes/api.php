<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthWorker\HealthWorkerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//creating a route for health workers by grouping prefix v1/healthworkers
Route::prefix('v1/healthworkers')->group(function () {
    Route::get('/', [HealthWorkerController::class, 'index']);
    Route::get('/{id}', [HealthWorkerController::class, 'show']);
    Route::post('/', [HealthWorkerController::class, 'store']);
    Route::put('/{id}', [HealthWorkerController::class, 'update']);
    Route::delete('/{id}', [HealthWorkerController::class, 'destroy']);
    //search
    Route::get('/search', [HealthWorkerController::class, 'search']);
    //pages
    Route::get('/page/{page}', [HealthWorkerController::class, 'getPage']);
});
