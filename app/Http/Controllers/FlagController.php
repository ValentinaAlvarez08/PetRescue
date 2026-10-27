<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlagRequest;
use App\Models\Comment;
use App\Models\PetReport;
use App\Models\Post;
use App\Notifications\ContentHiddenNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

/**
 * Sprint 4 — HU-20: denunciar reportes, publicaciones y comentarios.
 *
 * Sin login, cada persona se identifica con un hash de su IP: puede
 * denunciar un mismo contenido una sola vez. Al llegar al mínimo de
 * denuncias el contenido se oculta y se avisa a moderación.
 */
class FlagController extends Controller
{
    public function report(StoreFlagRequest $request, PetReport $report): RedirectResponse
    {
        abort_if($report->isHidden(), 404);

        return $this->flag($request, $report, fn () => route('reports.show', $report), fn () => route('reports.index'));
    }

    public function post(StoreFlagRequest $request, string $topic, Post $post): RedirectResponse
    {
        abort_unless($post->topic === $topic && ! $post->isHidden(), 404);

        return $this->flag($request, $post, fn () => route('posts.show', [$topic, $post]), fn () => route('community.show', $topic));
    }

    public function comment(StoreFlagRequest $request, Comment $comment): RedirectResponse
    {
        abort_if($comment->isHidden(), 404);
        $back = fn () => route('posts.show', [$comment->post->topic, $comment->post]).'#comentarios';

        return $this->flag($request, $comment, $back, $back);
    }

    /**
     * @param  callable(): string  $backUrl    a dónde volver si sigue visible
     * @param  callable(): string  $hiddenUrl  a dónde volver si se acaba de ocultar
     */
    private function flag(StoreFlagRequest $request, PetReport|Post|Comment $content, callable $backUrl, callable $hiddenUrl): RedirectResponse
    {
        $flag = $content->flags()->firstOrNew([
            'reporter_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        if ($flag->exists) {
            return redirect()->to($backUrl())
                ->with('status', 'Ya habías denunciado este contenido. Gracias, lo tendremos en cuenta.');
        }

        $flag->fill($request->validated())->save();

        if (! $content->hideIfFlaggedEnough()) {
            return redirect()->to($backUrl())
                ->with('status', 'Gracias por avisar. Si más personas lo denuncian, se ocultará mientras lo revisamos.');
        }

        if ($moderator = config('community.moderation.email')) {
            Notification::route('mail', $moderator)->notify(new ContentHiddenNotification($content));
        }

        return redirect()->to($hiddenUrl())
            ->with('status', 'Gracias por avisar. El contenido quedó oculto mientras lo revisamos.');
    }
}
