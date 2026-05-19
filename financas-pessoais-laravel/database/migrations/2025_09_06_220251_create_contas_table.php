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
        Schema::create('contas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->boolean('carteira')->default(false);
            $table->string('nome');
            $table->decimal('saldo', 19, 4)->default(0);
            $table->decimal('saldo_investido', 19, 4)->default(0);
            $table->boolean('somar_tela_inicial')->default(true);
            $table->uuid('imagem_id')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('imagem_id')->references('id')->on('imagens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas');
    }
};