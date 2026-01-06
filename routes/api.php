<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\DoseTrackController;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

use App\Http\Middleware\SetLocale;

Route::middleware([SetLocale::class])->group(function () {
    //Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        // Delete token
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('users', UserController::class);

        // Profile
        Route::prefix('profile')->controller(UserController::class)->group(function () {
            Route::get('/', [UserController::class, 'getUserById']);
            Route::put('/', [UserController::class, 'updateUser']);
            Route::delete('/', [UserController::class, 'deleteUser']);
        });

        // Relation
        Route::prefix('relation')->controller(RelationController::class)->group(function () {
            Route::post('/', 'createRelation');
            Route::get('/caregiverList', 'getCaregiverList');
            Route::get('/patientList', 'getPatientList');
            Route::delete('/deleteRelation', 'deleteRelation');
            Route::delete('/deleteCaregivers', 'deleteCaregivers');
            Route::delete('/deletePatients', 'deletePatients');
        });
        
        // Treatment
        Route::prefix('treatment')->controller(TreatmentController::class)->group(function () {
            Route::post('/', 'createTreatment');
            Route::get('/byPatient', 'byPatient');
            Route::get('/byDate', 'byDate');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        // Medicament
        Route::prefix('medicament')->controller(MedicamentController::class)->group(function () {
            Route::post('/', 'createMedicament');
            Route::get('/{id}', 'getMedicamentList');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'delete');
        });

        // Dose-track
        Route::prefix('dose-track')->controller(DoseTrackController::class)->group(function () {
            Route::get('/trackById/{id}', [DoseTrackController::class, 'byId']);
            Route::get('/trackByMedicament/{medicament_id}', [DoseTrackController::class, 'byMedicament']);
            Route::get('/trackBySchedule', [DoseTrackController::class, 'bySchedule']);

            Route::post('/', [DoseTrackController::class, 'createTrack']);
            Route::put('/{id}', [DoseTrackController::class, 'update']);
            Route::delete('/trackById/{id}', [DoseTrackController::class, 'delete']);
            Route::delete('/trackByMedicament/{medicament_id}', [DoseTrackController::class, 'deleteByMedicament']);
        });
    });
});
