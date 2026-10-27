<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 4 — HU-19: los negocios pueden pedir entrar al directorio.
     * Llegan como "pendiente" (is_active = false) hasta que un moderador los
     * apruebe. Los que ya estaban en el directorio quedan "aprobado".
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('review_status', 15)->default('aprobado')->after('is_active');
            $table->string('requester_name', 80)->nullable()->after('review_status');
            $table->string('requester_email', 150)->nullable()->after('requester_name');
            $table->string('review_note', 300)->nullable()->after('requester_email');
            $table->timestamp('reviewed_at')->nullable()->after('review_note');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'requester_name', 'requester_email', 'review_note', 'reviewed_at']);
        });
    }
};
