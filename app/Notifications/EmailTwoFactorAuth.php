<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailTwoFactorAuth extends Notification implements ShouldQueue
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
        app()->setlocale($this->lang);

        return (new MailMessage)
            ->subject(__('notifications.subject'))
            ->greeting(__('notifications.greeting'))
            ->line(__('notifications.anti_fishing') . $this->secretAntiFishing)
            ->line(__('notifications.code') . number_format($this->code, 0, ' ', ' '))
            ->salutation(__('notifications.thanks'));
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
