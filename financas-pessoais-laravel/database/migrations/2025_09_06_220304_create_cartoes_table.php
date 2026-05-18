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
        Schema::create('cartoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('conta_id');
            $table->string('nome');
            $table->enum('tipo', ['MULTIPLO', 'CREDITO', 'DEBITO']);
            $table->decimal('fatura_total', 19, 4)->default(0);
            $table->decimal('limite_total', 19, 4)->default(0);
            $table->date('data_fechamento')->nullable();
            $table->uuid('imagem_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('conta_id')->references('id')->on('contas')->onDelete('cascade');
            $table->foreign('imagem_id')->references('id')->on('imagens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartoes');
    }
};