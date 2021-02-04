<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailTwoFactorAuth extends Notification
{
    use Queueable;

    public $code;

    public $secretAntiFishing;

    public $lang;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($twoFactorCode, $secretAntiFishing, $preferredLang)
    {
        $this->code = $twoFactorCode;

        $this->secretAntiFishing = $secretAntiFishing;

        $this->lang = $preferredLang;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting('Hello!')
                    ->line('This is an official PasuSewa email and this is the proof: ' . $this->secretAntiFishing)
                    ->line('The 2 Factor Authentication Code is: ' . $this->code)
                    ->line('Your preferred language was: ' . $this->lang);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
