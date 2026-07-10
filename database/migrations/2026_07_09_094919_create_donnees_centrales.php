<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->decimal('prix_mensuel', 12, 2);
            $table->decimal('prix_annuel', 12, 2)->nullable();
            $table->unsignedInteger('limite_agences')->nullable();
            $table->unsignedInteger('limite_agents')->nullable();
            $table->unsignedInteger('limite_clients')->nullable();
            $table->text('fonctionnalites')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('periodicite')->default('mensuel');
            $table->string('statut')->default('actif');
            $table->timestamps();
        });

        Schema::create('factures_saas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abonnement_id')->constrained('abonnements');
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->decimal('montant', 12, 2);
            $table->date('date_emission')->default(now());
            $table->date('date_echeance');
            $table->string('statut')->default('en_attente');
            $table->date('date_paiement')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->text('description')->nullable();
        });

        Schema::create('grille_tarifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_id')->constrained('categories');
            $table->unsignedInteger('cv_min');
            $table->unsignedInteger('cv_max');
            $table->string('carburant',20);
            $table->string('type_client',30);
            $table->string('duree',20);
            $table->string('place')->nullable();
            $table->string('poids')->nullable();
            $table->decimal('montant', 12, 2);
            $table->date('date_debut_validite')->default(now());
            $table->date('date_fin_validite')->nullable();
            $table->timestamps();

            $table->index(['categorie_id', 'carburant', 'type_client', 'duree', 'cv_min', 'cv_max'], 'idx_tarifs_recherche');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grille_tarifs');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('factures_saas');
        Schema::dropIfExists('abonnements');
        Schema::dropIfExists('plans');
    }
};
