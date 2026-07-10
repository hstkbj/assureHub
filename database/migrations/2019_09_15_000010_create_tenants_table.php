<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('nom_commercial')->nullable();
            $table->string('raison_sociale')->nullable();
            $table->string('numero_agrement')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse_siege')->nullable();
            $table->string('ville_siege')->nullable();
            $table->string('sous_domaine')->nullable();
            $table->string('pays')->default('Bénin');
            $table->enum('statut', ['actif', 'suspendu', 'desactive'])->default('actif'); // actif / suspendu / desactive

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
