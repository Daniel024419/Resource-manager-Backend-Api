<?php

namespace App\Notifications\V1\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendAdminDefaultCredentials extends Notification
{
    use Queueable;
    private $title;
    private $email;
    private $firstName;
    private $lastName;
    private $password;


    /**
     * Create a new notification instance.
     *@var string title
     *@var string userId
     *@var string email
     *@var string firstName
     * @var string $lastName
     *@var string $password
     * @param string $title ,$email ,$password
     * @param string $sender_email
     */
    public function __construct(
        string $title,
        string $email,
        string $firstName,
        string $lastName,
        string $password

    ) {
        // Assign the provided to the property
        $this->title = $title;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = $password;
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
        $endPoint = env('FRONTEND_ACCESS_API_URL');
        $actionUrl = url($endPoint);

        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.admin_default_credentials', [
                'title' => $this->title,
                'email' => $this->email,
                'name' => $this->firstName . " " . $this->lastName,
                'password' => $this->password,
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
        return [
            //
        ];
    }
}
