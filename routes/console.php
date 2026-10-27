<?php

use App\Models\Business;
use App\Models\Comment;
use App\Models\PetReport;
use App\Models\Post;
use App\Notifications\BusinessRequestedNotification;
use App\Notifications\ContentHiddenNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sprint 4 — HU-19 / HU-20: lo pendiente de moderación, con su enlace firmado
// de revisión (útil si no se configuró MODERATION_EMAIL).
Artisan::command('moderacion:pendientes', function () {
    $businesses = Business::pending()->orderBy('created_at')->get();
    $this->info("Negocios por revisar: {$businesses->count()}");
    foreach ($businesses as $business) {
        $this->line("- {$business->name} ({$business->requester_email})");
        $this->line('  '.BusinessRequestedNotification::reviewUrl($business));
    }

    $hidden = collect([PetReport::class, Post::class, Comment::class])
        ->flatMap(fn (string $class) => $class::whereNotNull('hidden_at')->orderBy('hidden_at')->get());
    $this->info("Contenido oculto por denuncias: {$hidden->count()}");
    foreach ($hidden as $content) {
        $this->line('- '.$content->flagLabel());
        $this->line('  '.ContentHiddenNotification::reviewUrl($content));
    }
})->purpose('Lista los negocios por aprobar y el contenido oculto por denuncias');
