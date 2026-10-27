<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 2 — HU-13 (formulario guiado) y HU-06 (contacto directo):
     * sexo de la mascota, fecha en que se perdió / encontró y si el
     * número de contacto tiene WhatsApp.
     */
    public function up(): void
    {
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->string('sex', 10)->nullable()->after('size');
            $table->date('event_date')->nullable()->after('sex');
            $table->boolean('contact_whatsapp')->default(true)->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->dropColumn(['sex', 'event_date', 'contact_whatsapp']);
        });
    }
};
