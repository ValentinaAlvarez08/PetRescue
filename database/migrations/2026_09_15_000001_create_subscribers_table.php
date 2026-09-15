<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU3: usuarios (sin cuenta) que quieren recibir un aviso por correo
     * cuando se publique un reporte cerca de la ubicación que registraron.
     */
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('email');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('radius_km', 5, 2)->default(5);

            $table->boolean('notifications_enabled')->default(true);

            // Permite darse de baja sin necesidad de login (mismo patrón
            // del management_token de pet_reports).
            $table->uuid('unsubscribe_token')->unique();

            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
