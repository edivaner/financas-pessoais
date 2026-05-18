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
        Schema::create('categorias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable(); // null para categorias padrão do sistema
            $table->string('nome');
            $table->enum('tipo', ['DESPESAS', 'CREDITO', 'INVESTIMENTOS']);
            $table->string('icone')->nullable();
            $table->string('cor')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('essencial')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};