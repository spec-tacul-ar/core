<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use App\Models\Invitation;

class InvitationForNewAccount extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invitation $invitation)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return new MailMessage()
            ->subject('You have been invited to collaborate on a specification')
            ->greeting('Hello! We are Spectacular.')
            ->line(Str::escapeMarkdown($this->invitation->account->name) . ' has invited you to collaborate on their project: ' . Str::escapeMarkdown($this->invitation->project->name))
            ->line('You will need to register account first. Your invitation will be waiting for you when you log in.')
            ->action('Register', url('/app/register'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
