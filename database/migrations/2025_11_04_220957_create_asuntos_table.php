<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asuntos', function (Blueprint $table) {
            $table->id();
            $table->integer('orden');
            $table->string('tipo_asunto');
            $table->string('descripcion');
            $table->foreignId('agenda_id')->nullable()->constrained('agendas');
            $table->foreignId('miembro_id')->nullable()->constrained('members');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asuntos');
    }
};
