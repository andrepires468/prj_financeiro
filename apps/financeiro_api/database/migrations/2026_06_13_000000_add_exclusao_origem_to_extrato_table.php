<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Extrato', function (Blueprint $table) {
            $table->boolean('Excluido')->default(false)->after('CriadoEm');
            $table->unsignedInteger('ExcluidoPor')->nullable()->after('Excluido');
            $table->dateTime('ExcluidoEm')->nullable()->after('ExcluidoPor');
            $table->dateTime('RestauradoEm')->nullable()->after('ExcluidoEm');
            $table->string('Origem', 20)->default('integracao_proc')->after('RestauradoEm');

            $table->foreign('ExcluidoPor')->references('Id')->on('Usuario');
            $table->index(['IdBancoExtrato', 'Excluido', 'Data'], 'extrato_banco_excluido_data_idx');
        });

        DB::table('Extrato')->update([
            'Origem' => 'integracao_proc',
            'Excluido' => false,
        ]);
    }

    public function down(): void
    {
        Schema::table('Extrato', function (Blueprint $table) {
            $table->dropForeign(['ExcluidoPor']);
            $table->dropIndex('extrato_banco_excluido_data_idx');
            $table->dropColumn(['Excluido', 'ExcluidoPor', 'ExcluidoEm', 'RestauradoEm', 'Origem']);
        });
    }
};
