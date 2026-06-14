<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE Extrato MODIFY Identificador VARCHAR(500) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE Extrato MODIFY Identificador VARCHAR(40) NOT NULL');
    }
};
