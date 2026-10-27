<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 4 — HU-20: denuncias de contenido inapropiado sobre reportes,
     * publicaciones y comentarios. reporter_hash es un hash de la IP (no se
     * guarda la IP): una persona cuenta una sola vez por contenido.
     * Al llegar al mínimo de denuncias el contenido se oculta (hidden_at).
     */
    public function up(): void
    {
        Schema::create('content_flags', function (Blueprint $table) {
            $table->id();
            $table->morphs('flaggable');
            $table->string('reason', 30);              // llave de config('community.flags.reasons')
            $table->string('details', 300)->nullable();
            $table->string('reporter_hash', 64);
            $table->timestamps();

            $table->unique(['flaggable_type', 'flaggable_id', 'reporter_hash']);
        });

        foreach (['pet_reports', 'posts', 'comments'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->timestamp('hidden_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['pet_reports', 'posts', 'comments'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('hidden_at');
            });
        }

        Schema::dropIfExists('content_flags');
    }
};
