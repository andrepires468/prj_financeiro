<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TipoExtrato', function (Blueprint $table) {
            $table->unsignedTinyInteger('Id')->primary();
            $table->string('Nome', 50);
        });

        DB::table('TipoExtrato')->insert([
            ['Id' => 1, 'Nome' => 'Débito'],
            ['Id' => 2, 'Nome' => 'Crédito'],
        ]);

        Schema::table('Extrato', function (Blueprint $table) {
            $table->foreign('IdTipoExtrato')->references('Id')->on('TipoExtrato');
        });
    }

    public function down(): void
    {
        Schema::table('Extrato', function (Blueprint $table) {
            $table->dropForeign(['IdTipoExtrato']);
        });

        Schema::dropIfExists('TipoExtrato');
    }
};
