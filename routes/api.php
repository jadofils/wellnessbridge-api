<?php

use App\Http\Controllers\BirthProperty\BirthPropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthWorker\HealthWorkerController;
use App\Http\Controllers\Cadre\CadreController;
use App\Http\Controllers\Child\ChildController;
use App\Http\Controllers\HealthRecords\ChildHealthRecordController;

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
//group of cadre routes
Route::prefix('v1')->group(function () {
    Route::get('cadres', [CadreController::class, 'index']);
    Route::get('cadres/page/{page}', [CadreController::class, 'paginateByPage']);
    Route::post('cadres', [CadreController::class, 'store']);
    Route::get('cadres/{cadre}', [CadreController::class, 'show']);
    Route::put('cadres/{cadre}', [CadreController::class, 'update']);
    Route::delete('cadres/{cadre}', [CadreController::class, 'destroy']);
});

//group of child routes
Route::prefix('v1/children')->group(function () {
    Route::get('/', [ChildController::class, 'index']);
    Route::get('/{child}', [ChildController::class, 'show']);
    Route::post('/', [ChildController::class, 'store']);
    Route::put('/{child}', [ChildController::class, 'update']);
    Route::delete('/{child}', [ChildController::class, 'destroy']);
});
//creating a route for birth properties by grouping prefix v1/birth-properties
Route::prefix('v1/birth-properties')->group(function () {
    Route::get('/', [BirthPropertyController::class, 'index']); // Fixed typo
    Route::get('/{bID}', [BirthPropertyController::class, 'show']);
    Route::post('/', [BirthPropertyController::class, 'store']);
    Route::put('/{bID}', [BirthPropertyController::class, 'update']);
    Route::delete('/{bID}', [BirthPropertyController::class, 'destroy']);
    Route::get('/by-child/{childID}', [BirthPropertyController::class, 'showByChildID']);
    Route::delete('/by-child/{childID}', [BirthPropertyController::class, 'deleteByChildID']);
    Route::put('/by-child/{childID}', [BirthPropertyController::class, 'updateByChildID']);
});

//creating routes for child health records
Route::prefix('v1/child-health-records')->group(function () {
    Route::get('/', [ChildHealthRecordController::class, 'index']);
    Route::get('/{recordID}', [ChildHealthRecordController::class, 'show']);
    Route::post('/', [ChildHealthRecordController::class, 'store']);
    Route::delete('/{recordID}', [ChildHealthRecordController::class, 'destroy']);
    Route::get('/by-child/{childID}', [ChildHealthRecordController::class, 'getByChildID']);
    Route::post('/by-child/{childID}', [ChildHealthRecordController::class, 'addByChildID']);
    Route::put('/by-child/{childID}', [ChildHealthRecordController::class, 'updateByChildID']);
    Route::get('/by-health-worker/{hwID}', [ChildHealthRecordController::class, 'getByHealthWorkerID']);
    Route::post('/by-health-worker/{hwID}', [ChildHealthRecordController::class, 'addByHealthWorkerID']);
    Route::put('/by-health-worker/{hwID}', [ChildHealthRecordController::class, 'updateByHealthWorkerID']);

});

