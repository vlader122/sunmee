<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prays', function (Blueprint $table) {
            $table->string('orante_nombre')->nullable()->after('miembro_id');
        });
    }

    public function down(): void
    {
        Schema::table('prays', function (Blueprint $table) {
            $table->dropColumn('orante_nombre');
        });
    }
};
