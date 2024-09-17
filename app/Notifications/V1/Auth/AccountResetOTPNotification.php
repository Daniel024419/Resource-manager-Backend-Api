<?php

namespace App\Notifications\V1\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Mail\Message;
class AccountResetOTPNotification extends Notification
{
    use Queueable;

    // Define properties to hold data
    private $title;
    private $firstName;
    private $OTP;
    /**
     * Create a new notification instance.
     *@var string title
     *@var string email
     * @param string title , otp , email
     */
    public function __construct(
        string $title,
        string $firstName,
        string $OTP
    ) {
        // Assign the provided to the property
        $this->title = $title;
        $this->OTP = $OTP;
        $this->firstName = $firstName;
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


        return (new MailMessage)
        ->subject($this->title)
        ->view('emails.reset_otp', ['title' => $this->title, 'OTP' => $this->OTP, 'firstName' => $this->firstName]);


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