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

    public $anti_fishing_secret;

    public $lang;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($two_factor_code, $anti_fishing_secret, $preferred_lang)
    {
        $this->code = $two_factor_code;

        $this->anti_fishing_secret = $anti_fishing_secret;

        $this->lang = $preferred_lang;
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
            ->line(__('notifications.anti_fishing') . $this->anti_fishing_secret)
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
