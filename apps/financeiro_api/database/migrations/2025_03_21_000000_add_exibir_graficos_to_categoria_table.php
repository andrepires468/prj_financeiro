<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Categoria', function (Blueprint $table) {
            $table->boolean('ExibirGraficos')->default(true)->after('IconColor');
        });

        DB::table('Categoria')->whereIn('Id', [2, 31])->update(['ExibirGraficos' => 0]);
    }

    public function down(): void
    {
        Schema::table('Categoria', function (Blueprint $table) {
            $table->dropColumn('ExibirGraficos');
        });
    }
};
