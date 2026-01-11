<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix agendas table
        Schema::table('agendas', function (Blueprint $table) {
            // Cambiar tipo_agenda a tipo_agenda_id
            $table->integer('tipo_agenda_id')->nullable()->after('fecha');
        });

        // Eliminar columna tipo_agenda si existe
        if (Schema::hasColumn('agendas', 'tipo_agenda')) {
            Schema::table('agendas', function (Blueprint $table) {
                $table->dropColumn('tipo_agenda');
            });
        }

        // Hacer testimonios nullable
        DB::statement('ALTER TABLE agendas ALTER COLUMN testimonios DROP NOT NULL');

        // Fix messages table - hacer orden nullable
        DB::statement('ALTER TABLE messages ALTER COLUMN orden DROP NOT NULL');

        // Fix asuntos table - hacer orden nullable
        DB::statement('ALTER TABLE asuntos ALTER COLUMN orden DROP NOT NULL');
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('tipo_agenda')->nullable();
            $table->dropColumn('tipo_agenda_id');
        });
    }
};
