<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use App\Models\Contributor;

class InvitationAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Contributor $contributor)
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
            ->subject('Invitation accepted')
            ->line(Str::escapeMarkdown($this->contributor->account->name) . ' has accepted your invitation to collaborate on ' . Str::escapeMarkdown($this->contributor->project->name) . '.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
