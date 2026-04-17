<?php

namespace App\Mail;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentAdded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly Comment $comment,
        public readonly User $recipient,
        public readonly string $reason = 'assignee',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo comentario en: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comment-added',
        );
    }
}
