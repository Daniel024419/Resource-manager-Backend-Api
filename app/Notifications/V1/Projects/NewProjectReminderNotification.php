<?php

namespace App\Notifications\V1\Projects;

use App\Http\Requests\Project\ProjectNameRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProjectReminderNotification extends Notification
{
    use Queueable;

    // Define properties to hold data
    private $title, $firstName, $teamMembers, $details, $projectName, $startDate, $endDate, $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $title,
        string $firstName,
        string $projectName,
        string $teamMembers,
        string $details,
        $startDate,
        $endDate,
        string $sender,
    ) {
        // Assign the provided to the property
        $this->title = $title;
        $this->firstName = $firstName;
        $this->teamMembers = $teamMembers;
        $this->projectName = $projectName;
        $this->details = $details;
        $this->sender = $sender;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = url('https://rm.amalitech-dev.net');
        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.new_project_notification', [
                'title' => $this->title,
                'firstName' => $this->firstName,
                'teamMembers' => $this->teamMembers,
                'projectName' => $this->projectName,
                'details' => $this->details,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'sender' => $this->sender,
                'actionUrl' => $actionUrl
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}