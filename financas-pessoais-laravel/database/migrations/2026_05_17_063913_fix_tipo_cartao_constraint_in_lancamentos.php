<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table was already recreated without CHECK constraint during migration development.
        // Nothing to do if lancamentos already exists without constraint.
        if (!Schema::hasTable('lancamentos_old')) return;

        DB::statement('PRAGMA foreign_keys=OFF');
        DB::statement('INSERT INTO lancamentos (id,user_id,titulo,descricao,valor,tipo_lancamento,categoria_id,subcategoria_id,conta_origem_id,conta_destino_id,cartao_id,tipo_cartao,data,esta_pago,simulado,parcela_total,parcela_atual,imagem_id,created_at,updated_at,deleted_at,para_saldo_investido) SELECT id,user_id,titulo,descricao,valor,tipo_lancamento,categoria_id,subcategoria_id,conta_origem_id,conta_destino_id,cartao_id,tipo_cartao,data,esta_pago,0,parcela_total,0,imagem_id,created_at,updated_at,NULL,para_saldo_investido FROM lancamentos_old');
        DB::statement('DROP TABLE lancamentos_old');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        //
    }
};
