<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 3 — HU-17: publicaciones (preguntas y experiencias) en los
     * espacios de la comunidad. Sin login: el autor gestiona su publicación
     * con el enlace privado que contiene management_token.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('management_token')->unique();
            $table->string('topic', 30);                 // llave de config('community.topics')
            $table->string('nickname', 40);
            $table->string('title', 120);
            $table->text('body');                         // 20 a 2000 caracteres
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('helpful_count')->default(0); // HU-18: "Me sirvió"
            $table->timestamps();

            $table->index(['topic', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
