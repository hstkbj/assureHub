<?php

use App\Http\Controllers\AgenceController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AssuranceController;
use App\Http\Controllers\EcheanceController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\RappelController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Central\CategorieController;
use App\Http\Controllers\Central\GrilleTarifController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\AbonnementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/central')->group(function () {
    Route::post('/inscription', [InscriptionController::class, 'creerEntreprise']);

    Route::apiResource('categories', CategorieController::class);
    Route::apiResource('grille-tarifs', GrilleTarifController::class);
    Route::apiResource('plans', PlanController::class);
    Route::apiResource('abonnements', AbonnementController::class)->only(['index', 'store', 'show', 'update']);
});


Route::prefix('v1')->middleware(['tenant.api'])->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::apiResource('users', UserController::class);

        Route::apiResource('agences', AgenceController::class);

        Route::apiResource('clients', ClientController::class);
        Route::apiResource('assurances', AssuranceController::class);

        Route::get('assurances/{assurance}/echeances', [EcheanceController::class, 'parAssurance']);
        Route::post('echeances/{echeance}/payer', [PaiementController::class, 'payerEcheance']);

        Route::get('paiements', [PaiementController::class, 'index']);

        Route::get('paiements', [PaiementController::class, 'index']);
        Route::get('rappels', [RappelController::class, 'index']);
        Route::get('commissions', [CommissionController::class, 'index']);
        Route::get('activity-logs', [ActivityLogController::class, 'index']);
    });
});
