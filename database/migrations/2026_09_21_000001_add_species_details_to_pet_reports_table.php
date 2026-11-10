<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU1/HU2: campos estructurados (especie, raza, color, tamaño) que
     * antes solo vivían como texto libre en "description", más un medio
     * de contacto alternativo por correo.
     */
    public function up(): void
    {
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->string('species', 50)->after('type');
            $table->string('breed', 100)->nullable()->after('species');
            $table->string('color', 100)->after('breed');
            $table->string('size', 20)->nullable()->after('color');
            $table->string('contact_email')->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('pet_reports', function (Blueprint $table) {
            $table->dropColumn(['species', 'breed', 'color', 'size', 'contact_email']);
        });
    }
};
