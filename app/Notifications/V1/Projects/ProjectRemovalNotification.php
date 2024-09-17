<?php

namespace App\Notifications\V1\Projects;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectRemovalNotification extends Notification
{
    use Queueable;

    // Define properties to hold data
    private $title, $firstName, $details, $projectName, $sender;

    /**
     * Create a new notification instance.
     *
     * @param string $title       - The title of the notification.
     * @param string $firstName   - The first name of the recipient.
     * @param string $projectName - The name of the project related to the notification.
     * @param string $details     - Additional details or information for the notification.
     * @param string $sender      - The sender of the notification.
     */
    public function __construct(
        string $title,
        string $firstName,
        string $projectName,
        string $details,
        string $sender
    ) {
        // Assign the provided values to the properties
        $this->title = $title;
        $this->firstName = $firstName;
        $this->details = $details;
        $this->projectName = $projectName;
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
            ->view('emails.users_project_removal_notification', [
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