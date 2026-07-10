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
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\AbonnementController;
use App\Http\Controllers\Central\AdminPlateformeController;
use App\Http\Controllers\Central\CategorieController as CentralCategorieController;
use App\Http\Controllers\Central\GrilleTarifController as CentralGrilleTarifController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. ROUTES CENTRALES — pas de header X-Tenant nécessaire.
| Uniquement : inscription publique + supervision plateforme (admin only).
| categories/grille_tarifs/souscription ont déménagé côté tenant (section 2),
| car propres à chaque entreprise.
|--------------------------------------------------------------------------
*/
Route::prefix('v1/central')->group(function () {

    Route::post('/inscription', [InscriptionController::class, 'creerEntreprise']);
    Route::post('/admin/login', [AdminPlateformeController::class, 'login']);

    // Page tarifs publique — consultable sans connexion, pour convaincre
    // une entreprise de s'inscrire.
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{plan}', [PlanController::class, 'show']);

    Route::middleware(['auth:sanctum', 'admin.plateforme'])->group(function () {
        Route::post('/admin/logout', [AdminPlateformeController::class, 'logout']);
        Route::get('/admin/me', [AdminPlateformeController::class, 'me']);

        // Gestion des plans (création/modification/désactivation) + supervision
        // des abonnements de TOUTES les entreprises — réservé à l'admin plateforme.
        Route::apiResource('plans', PlanController::class)->except(['index', 'show']);
        Route::apiResource('abonnements', AbonnementController::class)->only(['index', 'show', 'update']);
    });
});


/*
|--------------------------------------------------------------------------
| 2. ROUTES TENANT — nécessitent le header X-Tenant
|--------------------------------------------------------------------------
*/
Route::prefix('v1')
    ->middleware(['tenant.api'])
    ->group(function () {

        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {

            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);

            // Catégories / grille tarifaire — PROPRES à cette entreprise.
            // Lecture pour tous les rôles, écriture réservée à admin_entreprise
            // (vérifié à l'intérieur des contrôleurs).
            Route::apiResource('categories', CentralCategorieController::class);
            Route::apiResource('grille-tarifs', CentralGrilleTarifController::class);

            // Abonnement de CETTE entreprise à un plan SaaS (self-service)
            Route::get('mon-abonnement', [AbonnementController::class, 'monAbonnement']);
            Route::post('souscrire', [AbonnementController::class, 'souscrire']);

            Route::apiResource('users', UserController::class);
            Route::apiResource('agences', AgenceController::class);
            Route::apiResource('clients', ClientController::class);
            Route::apiResource('assurances', AssuranceController::class);

            Route::get('assurances/{assurance}/echeances', [EcheanceController::class, 'parAssurance']);
            Route::post('echeances/{echeance}/payer', [PaiementController::class, 'payerEcheance']);

            Route::get('paiements', [PaiementController::class, 'index']);
            Route::get('rappels', [RappelController::class, 'index']);
            Route::get('commissions', [CommissionController::class, 'index']);
            Route::get('activity-logs', [ActivityLogController::class, 'index']);
        });
    });
