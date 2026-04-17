<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly User $recipient,
        public readonly string $previousStatus,
        public readonly User $changedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Estado actualizado: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-status-changed',
        );
    }
}
