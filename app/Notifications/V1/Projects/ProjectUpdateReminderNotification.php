<?php

namespace App\Notifications\V1\Projects;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectUpdateReminderNotification extends Notification
{
    use Queueable;

    private $title, $firstName, $details, $projectName, $sender;

    /**
     * Create a new notification instance.
     *
     * @param string $title
     * @param string $firstName
     * @param string $projectName
     * @param array $details
     * @param string $sender
     */
    public function __construct(
        string $title,
        string $firstName,
        string $projectName,
        array $details,
        string $sender
    ) {
        $this->title = $title;
        $this->firstName = $firstName;
        $this->details = $details;
        $this->projectName = $projectName;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.users_project_update_notification', [
                'title' => $this->title,
                'firstName' => $this->firstName,
                'projectName' => $this->projectName,
                'details' => $this->details,
                'sender' => $this->sender,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}