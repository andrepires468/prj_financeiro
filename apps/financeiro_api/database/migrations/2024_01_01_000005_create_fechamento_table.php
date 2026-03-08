<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFechamentoTable extends Migration
{
    public function up()
    {
        Schema::create('Fechamento', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('IdBancoExtrato');
            $table->decimal('ValorFechado', 15, 2);
            $table->date('DataBase');
            $table->dateTime('AlteradoEm');

            $table->foreign('IdBancoExtrato')->references('Id')->on('BancoExtrato');
        });
    }

    public function down()
    {
        Schema::dropIfExists('Fechamento');
    }
}
