<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriaTable extends Migration
{
    public function up()
    {
        Schema::create('Categoria', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('CategoriaNome', 200);
            $table->string('IconTag', 40);
            $table->string('IconColor', 6);
        });
    }

    public function down()
    {
        Schema::dropIfExists('Categoria');
    }
}
