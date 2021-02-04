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

    protected $translations = [
        "es" => [
            "greeting" => "Hola!",
            "anti_fishing" => "Este es un correo oficial de PasuSewa, y aquí está la prueba: ",
            "code" => "El Código de Autenticación es: ",
            "thanks" => "Gracias por confiar en PasuSewa."
        ],
        "en" => [
            "greeting" => "Hello!",
            "anti_fishing" => "This is an official PasuSewa email and this is the proof: ",
            "code" => "The Authentication Code is: ",
            "thanks" => "Thank you for trusting in PsauSewa"
        ],
        "jp" => [
            "greeting" => "こんにちは!",
            "anti_fishing" => "これはパス世話からの公式メールであり、その proof は次のとおりです。",
            "code" => "認証コードは次のとおりです。",
            "thanks" => "パス世話を信頼していただきありがとうございます。"
        ],
    ];

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
                    ->greeting($this->translations[$this->lang]["greeting"])
                    ->line($this->translations[$this->lang]["anti_fishing"] . $this->secretAntiFishing)
                    ->line($this->translations[$this->lang]["code"] . $this->code)
                    ->line($this->translations[$this->lang]["thanks"]);
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
