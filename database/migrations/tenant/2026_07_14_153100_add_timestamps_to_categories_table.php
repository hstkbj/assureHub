<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'created_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'created_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
