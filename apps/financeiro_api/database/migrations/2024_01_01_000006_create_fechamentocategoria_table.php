<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFechamentocategoriaTable extends Migration
{
    public function up()
    {
        Schema::create('FechamentoCategoria', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('IdBancoExtrato');
            $table->unsignedInteger('IdCategoria');
            $table->decimal('ValorFechado', 15, 2);
            $table->date('DataBase');
            $table->dateTime('AlteradoEm');

            $table->foreign('IdBancoExtrato')->references('Id')->on('BancoExtrato');
            $table->foreign('IdCategoria')->references('Id')->on('Categoria');
        });
    }

    public function down()
    {
        Schema::dropIfExists('FechamentoCategoria');
    }
}
