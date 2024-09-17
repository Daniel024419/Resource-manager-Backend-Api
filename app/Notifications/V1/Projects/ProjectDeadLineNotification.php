<?php

namespace App\Notifications\V1\Projects; // Corrected namespace

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectDeadLineNotification extends Notification
{
    use Queueable;

    // Define properties to hold data
    private $title, $firstName, $projects, $sender;

    /**
     * Create a new notification instance.
     *
     * @param string $title     - The title of the notification.
     * @param string $firstName - The first name of the recipient.
     * @param array $projects   - An array containing project information.
     * @param string $sender    - The sender of the notification.
     */
    public function __construct(
        string $title,
        string $firstName,
        array $projects,
        string $sender
    ) {
        // Assign the provided values to the properties
        $this->title = $title;
        $this->firstName = $firstName;
        $this->projects = $projects;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable - The notifiable object.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Specify the delivery channel(s), in this case, mail
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable - The notifiable object.
     *
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Configure the mail message with the provided data
        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.project_deadline_notification', [
                'title' => $this->title,
                'firstName' => $this->firstName,
                'projects' => $this->projects,
                'sender' => $this->sender, // Add sender to the view data
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable - The notifiable object.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Return an empty array, as we are not using this representation for now
        return [];
    }
}