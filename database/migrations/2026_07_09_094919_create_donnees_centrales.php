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


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('factures_saas');
        Schema::dropIfExists('abonnements');
        Schema::dropIfExists('plans');
    }
};
