<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public $lang;

    public $anti_fishing_secret;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($secret, $preferred_lang)
    {
        $this->lang = $preferred_lang;

        $this->anti_fishing_secret = $secret;
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
            ->subject(__('notifications.payments.error_subject'))
            ->greeting(__('notifications.greeting'))
            ->line(__('notifications.anti_fishing') . $this->anti_fishing_secret)
            ->line(__('notifications.payments.error_body'))
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
