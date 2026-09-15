<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU1: reportar mascota perdida sin cuenta
     * HU2: reportar mascota encontrada sin cuenta
     * HU3: soporta consultas de "reportes cercanos" (lat/lng) para notificar
     *      a la comunidad cercana sin necesidad de login.
     */
    public function up(): void
    {
        Schema::create('pet_reports', function (Blueprint $table) {
            $table->id();

            // Identificador privado (no es login): quien crea el reporte
            // recibe un enlace con este token para gestionar su caso.
            $table->uuid('management_token')->unique();

            $table->enum('type', ['perdida', 'encontrada']);
            $table->string('pet_name')->nullable();
            $table->text('description');
            $table->string('photo_path')->nullable();

            // Contacto directo, sin necesidad de cuenta
            $table->string('contact_phone', 30);

            // Geolocalización para notificaciones por cercanía (HU3)
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_reference')->nullable();

            $table->enum('status', ['activo', 'reunido', 'cerrado'])->default('activo');

            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_reports');
    }
};
