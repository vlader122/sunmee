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
        Schema::create('himn_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('himno_id')->nullable()->constrained('himns');
            $table->foreignId('agenda_id')->nullable()->constrained('agendas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('himn_agendas');
    }
};
