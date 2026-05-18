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
        Schema::table('limites', function (Blueprint $table) {
            $table->string('titulo', 25)->change();
            $table->string('descricao', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('limites', function (Blueprint $table) {
            $table->string('titulo')->change();
            $table->text('descricao')->nullable()->change();
        });
    }
};
