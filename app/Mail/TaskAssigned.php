<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly User $assignee,
        public readonly User $assignedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Te han asignado: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-assigned',
        );
    }
}
