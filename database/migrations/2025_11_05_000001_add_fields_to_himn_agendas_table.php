<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('himn_agendas', function (Blueprint $table) {
            $table->integer('numero')->nullable()->after('id');
            $table->string('titulo')->nullable()->after('numero');
            $table->string('tipo')->nullable()->after('titulo'); // apertura, sacramental, intermedio, cierre
        });
    }

    public function down(): void
    {
        Schema::table('himn_agendas', function (Blueprint $table) {
            $table->dropColumn(['numero', 'titulo', 'tipo']);
        });
    }
};
