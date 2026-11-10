<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 4 — HU-12: fecha del reencuentro, para mostrar las historias de
     * éxito más recientes. A los reportes que ya estaban reunidos se les
     * asigna su última actualización (el momento en que se marcaron).
     */
    public function up(): void
    {
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->timestamp('reunited_at')->nullable()->after('status');
        });

        DB::table('pet_reports')
            ->where('status', 'reunido')
            ->update(['reunited_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->dropColumn('reunited_at');
        });
    }
};
