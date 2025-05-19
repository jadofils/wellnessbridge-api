<?php

use App\Http\Controllers\BirthProperty\BirthPropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthWorker\HealthWorkerController;
use App\Http\Controllers\Cadre\CadreController;
use App\Http\Controllers\Child\ChildController;
use App\Http\Controllers\HealthRecords\ChildHealthRecordController;
use App\Http\Controllers\HealthRecords\HealthRestrictionController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\assignProject\AssignProjectController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use Illuminate\Support\Facades\DB;

Route::get('/check-db', function () {
    try {
        // Fetch the database configuration
        $dbConfig = [
            'DB_CONNECTION' => env('DB_CONNECTION'),
            'DB_HOST' => env('DB_HOST'),
            'DB_PORT' => env('DB_PORT'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
            'DB_PASSWORD' => env('DB_PASSWORD') ? '********' : 'Not Set', // Hide password for security
        ];

        // Test connection
        DB::connection()->getPdo();
        return response()->json([
            'status' => '✅ Database connection successful!',
            'config' => $dbConfig,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => '❌ Database connection failed!',
            'error' => $e->getMessage(),
        ]);
    }
});



//creating a route for health workers by grouping prefix v1/healthworkers
Route::prefix('v1/healthworkers')->group(function () {
    Route::get('/', [HealthWorkerController::class, 'index']);
    Route::get('/{id}', [HealthWorkerController::class, 'show']);
    Route::post('/', [HealthWorkerController::class, 'store']);
    Route::put('/{id}', [HealthWorkerController::class, 'update']);
    Route::delete('/{id}', [HealthWorkerController::class, 'destroy']);
    //assigning health worker to a cadre
    Route::post('/assign', [HealthWorkerController::class, 'AssignHealthWorkToCadre']);
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

//health worker restricting routes
Route::prefix('v1/health-restrictions')->group(function () {
    Route::get('/', [HealthRestrictionController::class, 'index']);
    Route::get('/{hrID}', [HealthRestrictionController::class, 'show']);
    Route::post('/by-health-worker/{hwID}', [HealthRestrictionController::class, 'addByHealthWorkerID']);
    Route::put('/by-health-worker/{hwID}', [HealthRestrictionController::class, 'updateByHealthWorkerID']);
    Route::delete('/{hrID}', [HealthRestrictionController::class, 'destroy']);
    Route::get('/by-child/{childID}', [HealthRestrictionController::class, 'getByChildID']);
    Route::post('/by-child/{childID}', [HealthRestrictionController::class, 'addByChildID']);
    Route::delete('/by=child/{childId}', [HealthRestrictionController::class, 'destroyByChildID']);

});
//project routes
// Project Routes
Route::prefix('v1/projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);        // Get all projects
    Route::get('/{id}', [ProjectController::class, 'show']);     // Get single project by ID
    Route::post('/', [ProjectController::class, 'store']);       // Create new project
    Route::put('/{id}', [ProjectController::class, 'update']);   // Update project by ID
    Route::delete('/{id}', [ProjectController::class, 'destroy']);// Delete project by ID
});


Route::prefix('v1/project-assignments')->group(function () {
    Route::get('/', [AssignProjectController::class, 'index']);
    Route::get('/{id}', [AssignProjectController::class, 'show']);
    Route::post('/', [AssignProjectController::class, 'store']);
    Route::put('/{id}', [AssignProjectController::class, 'update']);
    Route::delete('/{id}', [AssignProjectController::class, 'destroy']);
});
