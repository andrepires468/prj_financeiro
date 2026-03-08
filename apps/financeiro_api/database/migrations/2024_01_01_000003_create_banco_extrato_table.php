<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBancoExtratoTable extends Migration
{
    public function up()
    {
        Schema::create('BancoExtrato', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('BancoNome', 200);
            $table->unsignedInteger('IdUsuario');
            $table->boolean('Default');
            $table->string('GmailUser', 500)->nullable();
            $table->string('GmailPass', 500)->nullable();

            $table->foreign('IdUsuario')->references('Id')->on('Usuario');
        });
    }

    public function down()
    {
        Schema::dropIfExists('BancoExtrato');
    }
}
