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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('tipo_agenda');
            $table->text('testimonios')->nullable();
            $table->foreignId('presidido_por_id')->nullable()->constrained('members');
            $table->foreignId('dirigido_por_id')->nullable()->constrained('members');
            $table->foreignId('pianista_id')->nullable()->constrained('members');

            // Esta no es nula
            $table->foreignId('director_himno_id')->constrained('members');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
