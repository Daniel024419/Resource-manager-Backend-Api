<?php

namespace App\Notifications\V1\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AccountCompletionNotification extends Notification
{
    use Queueable;

    // Define properties to hold data
    private $title;
    private $userId;
    private $email;
    private $roles;
    private $accessToken;
    private $sender;
    private $sender_email;
    /**
     * Create a new notification instance.
     *@var string title
     *@var string userId
     *@var string email
     *@var string roles
     *@var string userId
     * @param string $title , $userId ,$email
     * @param string  $sender
     * @param string $sender_email
     */
    public function __construct(
        string $title,
        string $userId,
        string $email,
        string $roles,
        string $accessToken,
        string $sender,
        string $sender_email
    ) {
        // Assign the provided to the property
        $this->title = $title;
        $this->email = $email;
        $this->userId = $userId;
        $this->roles = $roles;
        $this->accessToken = $accessToken;
        $this->sender = $sender;
        $this->sender_email = $sender_email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {

        //can add multiple channel
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {

        $endPoint =env('FRONTEND_ACCESS_API_URL').'/account-setup';
        $actionUrl = url($endPoint. '/'. $this->userId. '/'.$this->accessToken.'/'.$this->email.'/'.md5(rand()));

        return (new MailMessage)
        ->subject($this->title)
        ->view('emails.account_setup_invitation', [
        'title' => $this->title,
        'sender' => $this->sender,
        'receiver_email' => $this->email,
        'sender_email' => $this->sender_email,
        'actionUrl' => $actionUrl]);
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [

        ];
    }
}