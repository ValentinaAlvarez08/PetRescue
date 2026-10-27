<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 4 — HU-08: pares de reportes (perdida ↔ encontrada) que ya se
     * avisaron como posible coincidencia. Evita repetir el correo cuando un
     * reporte se edita (HU-07) y la coincidencia se vuelve a encontrar.
     */
    public function up(): void
    {
        Schema::create('report_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lost_report_id')->constrained('pet_reports')->cascadeOnDelete();
            $table->foreignId('found_report_id')->constrained('pet_reports')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['lost_report_id', 'found_report_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_matches');
    }
};
