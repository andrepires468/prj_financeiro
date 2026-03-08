<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioTable extends Migration
{
    public function up()
    {
        Schema::create('Usuario', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Nome', 200);
            $table->string('Email', 200)->unique();
            $table->string('Password', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('Usuario');
    }
}
