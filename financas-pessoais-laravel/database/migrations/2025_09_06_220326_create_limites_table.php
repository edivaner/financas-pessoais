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
        Schema::create('limites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('categoria_id');
            $table->string('titulo', 25);
            $table->string('descricao', 255)->nullable();
            $table->decimal('valor_limite', 19, 4);
            $table->decimal('valor_gasto_atual', 19, 4)->default(0);
            $table->enum('periodicidade', ['MENSAL'])->default('MENSAL');
            $table->date('ultimo_reset')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limites');
    }
};