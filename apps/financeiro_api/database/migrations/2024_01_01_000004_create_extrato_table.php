<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtratoTable extends Migration
{
    public function up()
    {
        Schema::create('Extrato', function (Blueprint $table) {
            $table->increments('Id');
            $table->date('Data');
            $table->decimal('Valor', 15, 2);
            $table->string('Identificador', 40);
            $table->string('Descricao', 420);
            $table->tinyInteger('IdTipoExtrato');
            $table->unsignedInteger('IdBancoExtrato');
            $table->unsignedInteger('IdCategoria')->nullable();
            $table->string('DescricaoPersonalizada', 200)->nullable();
            $table->text('DescricaoTokens')->nullable();
            $table->dateTime('CriadoEm')->nullable();

            $table->foreign('IdBancoExtrato')->references('Id')->on('BancoExtrato');
            $table->foreign('IdCategoria')->references('Id')->on('Categoria');
        });
    }

    public function down()
    {
        Schema::dropIfExists('Extrato');
    }
}
