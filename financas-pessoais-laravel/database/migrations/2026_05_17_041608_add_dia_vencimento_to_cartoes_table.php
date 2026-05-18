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
        Schema::table('cartoes', function (Blueprint $table) {
            $table->tinyInteger('dia_vencimento')->nullable()->after('limite_total');
            $table->tinyInteger('dias_antes_fechamento')->default(5)->after('dia_vencimento');
            $table->dropColumn('data_fechamento');
        });
    }

    public function down(): void
    {
        Schema::table('cartoes', function (Blueprint $table) {
            $table->date('data_fechamento')->nullable()->after('limite_total');
            $table->dropColumn(['dia_vencimento', 'dias_antes_fechamento']);
        });
    }
};
