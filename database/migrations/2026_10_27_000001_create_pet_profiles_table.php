<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 4 — HU-10: perfil digital de la mascota (sin login). El dueño lo
     * administra con management_token; public_code es el código corto del
     * enlace público y del QR de la placa (no se usa el id para que no se
     * puedan recorrer los perfiles uno por uno).
     */
    public function up(): void
    {
        Schema::create('pet_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('management_token')->unique();
            $table->string('public_code', 12)->unique();

            $table->string('name', 40);
            $table->string('species', 50);
            $table->string('breed', 100)->nullable();
            $table->string('sex', 10);
            $table->string('size', 20)->nullable();
            $table->string('color', 100);
            $table->date('birth_date')->nullable();
            $table->string('photo_path');
            $table->string('distinctive_marks', 500)->nullable();

            // Salud: lo que necesita saber quien la encuentre.
            $table->string('sterilized', 10)->default('no_se');
            $table->string('vaccines', 300)->nullable();
            $table->string('health_notes', 500)->nullable();

            $table->string('owner_name', 80);
            $table->string('contact_phone', 30);
            $table->boolean('contact_whatsapp')->default(true);
            $table->string('contact_email', 150)->nullable();
            $table->string('neighborhood', 150)->nullable();

            $table->timestamps();
        });

        // Un reporte de pérdida puede crearse desde el perfil (HU-10, CID 4).
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->foreignId('pet_profile_id')->nullable()->after('management_token')
                ->constrained('pet_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pet_profile_id');
        });

        Schema::dropIfExists('pet_profiles');
    }
};
