<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMesAnoInicioToBancoExtratoTable extends Migration
{
    public function up()
    {
        Schema::connection('home')->table('BancoExtrato', function (Blueprint $table) {
            $table->unsignedTinyInteger('MesInicio')->nullable()->after('GmailPass');
            $table->unsignedSmallInteger('AnoInicio')->nullable()->after('MesInicio');
        });
    }

    public function down()
    {
        Schema::connection('home')->table('BancoExtrato', function (Blueprint $table) {
            $table->dropColumn(['MesInicio', 'AnoInicio']);
        });
    }
}
