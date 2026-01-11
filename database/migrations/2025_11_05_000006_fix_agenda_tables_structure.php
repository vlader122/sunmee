<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix agendas table - agregar tipo_agenda_id solo si no existe
        if (!Schema::hasColumn('agendas', 'tipo_agenda_id')) {
            Schema::table('agendas', function (Blueprint $table) {
                $table->integer('tipo_agenda_id')->nullable();
            });
        }

        // Eliminar columna tipo_agenda si existe
        if (Schema::hasColumn('agendas', 'tipo_agenda')) {
            Schema::table('agendas', function (Blueprint $table) {
                $table->dropColumn('tipo_agenda');
            });
        }

        // Nota: SQLite no soporta ALTER COLUMN para cambiar nullability
        // Las columnas ya se manejan como nullable en las migraciones originales
    }

    public function down(): void
    {
        if (!Schema::hasColumn('agendas', 'tipo_agenda')) {
            Schema::table('agendas', function (Blueprint $table) {
                $table->string('tipo_agenda')->nullable();
            });
        }

        if (Schema::hasColumn('agendas', 'tipo_agenda_id')) {
            Schema::table('agendas', function (Blueprint $table) {
                $table->dropColumn('tipo_agenda_id');
            });
        }
    }
};
