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
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->string('nom_agence');
            $table->string('code_agence', 20)->unique()->nullable();
            $table->string('responsable')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('quartier')->nullable();
            $table->string('statut', 20)->default('actif');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone', 20)->nullable();
            $table->enum('role', ['admin_entreprise', 'admin_agence', 'commercial'])->default('commercial'); // admin_entreprise / admin_agence / commercial
            $table->string('password');
            $table->boolean('statut')->default(true);
            $table->string('photo')->nullable();
            $table->string('code_password')->nullable();
            $table->date('date_expiration_code_password')->nullable();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('nom');
            $table->string('prenom');
            $table->string('genre', 1)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('telephone', 20)->unique();
            $table->string('email')->nullable();
            $table->string('npi', 50)->unique()->nullable();
            $table->string('ville')->nullable();
            $table->string('quartier')->nullable();
            $table->timestamps();
        });

        Schema::create('assurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->string('code', 30)->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->unsignedBigInteger('categorie_id'); // référence logique vers la base centrale
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->string('immatricule', 20);
            $table->string('carburant', 20);
            $table->unsignedInteger('puissance_fiscale');
            $table->string('place', 20)->nullable();
            $table->string('poids', 20)->nullable();

            $table->string('duree', 20);
            $table->string('type_client', 30);
            $table->date('date_debut');
            $table->date('date_fin');

            $table->enum('frequence_paiement', ['journalier', 'hebdomadaire', 'mensuel','enBloc'])->default('mensuel'); // journalier / hebdomadaire / mensuel
            $table->decimal('montant_total', 12, 2);
            $table->decimal('montant_paye_cumule', 12, 2)->default(0);
            $table->enum('statut', ['enCours', 'enAttente', 'enRetard', 'enPause', ])->default('enCours');
            $table->timestamps();

            $table->index(['statut', 'date_fin']);
        });

        Schema::create('echeances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assurance_id')->constrained('assurances')->onDelete('cascade');
            $table->unsignedInteger('numero_echeance');
            $table->date('date_echeance');
            $table->decimal('montant_du', 12, 2);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->string('statut', 20)->default('impaye');
            $table->timestamps();

            $table->unique(['assurance_id', 'numero_echeance']);
            $table->index(['date_echeance', 'statut']);
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->foreignId('assurance_id')->constrained('assurances');
            $table->foreignId('echeance_id')->nullable()->constrained('echeances');
            $table->decimal('montant', 12, 2);
            $table->date('date_paiement')->default(now());
            $table->string('mode_paiement', 30)->nullable();
            $table->string('reference_transaction', 100)->nullable();
            $table->foreignId('encaisse_par')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('rappels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->foreignId('assurance_id')->constrained('assurances')->onDelete('cascade');
            $table->string('type_rappel', 30);
            $table->decimal('montant_calcule', 12, 2)->nullable();
            $table->date('date_prevue');
            $table->string('canal', 20)->nullable();
            $table->string('statut', 20)->default('en_attente');
            $table->timestamp('date_envoi')->nullable();
            $table->timestamps();

            $table->index(['date_prevue', 'statut']);
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->foreignId('assurance_id')->constrained('assurances');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('montant', 12, 2);
            $table->string('statut', 20)->default('enAttente');
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->string('ip_adresse', 45)->nullable();
            $table->timestamps();

            $table->index(['agence_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('rappels');
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('echeances');
        Schema::dropIfExists('assurances');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('agences');
    }
};
