<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 2 — HU-16: directorio de servicios para mascotas (tiendas,
     * guarderías, baño y peluquería, veterinarias y droguerías) con
     * ubicación para mostrarlos en el mapa.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('category', 30);          // llave de config('community.directory')
            $table->string('description', 500);       // a qué se dedica
            $table->string('services', 300)->nullable(); // lista corta separada por comas
            $table->string('phone', 30)->nullable();
            $table->boolean('has_whatsapp')->default(false);
            $table->string('email', 150)->nullable();
            $table->string('address', 200);
            $table->string('schedule', 150)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
