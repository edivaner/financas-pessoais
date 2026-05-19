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
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->decimal('valor', 19, 4);
            $table->enum('tipo_lancamento', ['RECEITAS', 'DESPESAS', 'TRANSFERENCIA', 'INVESTIMENTOS']);
            $table->uuid('categoria_id')->nullable();
            $table->uuid('subcategoria_id')->nullable();
            $table->uuid('conta_origem_id')->nullable();
            $table->uuid('conta_destino_id')->nullable();
            $table->boolean('para_saldo_investido')->default(false);
            $table->uuid('cartao_id')->nullable();
            $table->enum('tipo_cartao', ['DEBITO', 'CREDITO'])->nullable();
            $table->date('data');
            $table->boolean('esta_pago')->default(true);
            $table->boolean('simulado')->default(false);
            $table->integer('parcela_total')->nullable();
            $table->integer('parcela_atual')->default(1);
            $table->uuid('imagem_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            $table->foreign('subcategoria_id')->references('id')->on('subcategorias')->onDelete('set null');
            $table->foreign('conta_origem_id')->references('id')->on('contas')->onDelete('cascade');
            $table->foreign('conta_destino_id')->references('id')->on('contas')->onDelete('cascade');
            $table->foreign('cartao_id')->references('id')->on('cartoes')->onDelete('cascade');
            $table->foreign('imagem_id')->references('id')->on('imagens')->onDelete('set null');

            $table->index(['user_id', 'data']);
            $table->index(['user_id', 'tipo_lancamento']);
            $table->index(['conta_origem_id', 'data']);
            $table->index(['cartao_id', 'data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};