<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Hacer columnas nullable (PostgreSQL)
        if (Schema::hasColumn('agendas', 'testimonios')) {
            DB::statement('ALTER TABLE agendas ALTER COLUMN testimonios DROP NOT NULL');
        }

        if (Schema::hasColumn('messages', 'orden')) {
            DB::statement('ALTER TABLE messages ALTER COLUMN orden DROP NOT NULL');
        }

        if (Schema::hasColumn('asuntos', 'orden')) {
            DB::statement('ALTER TABLE asuntos ALTER COLUMN orden DROP NOT NULL');
        }
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
