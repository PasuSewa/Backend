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

    protected $translations = [
        "es" => [
            "subject" => "Códico de Autenticación PasuNashi",
            "greeting" => "Hola!",
            "anti_fishing" => "Este es un correo oficial de PasuNashi, y aquí está la prueba: ",
            "code" => "Tu Código de Autenticación es: ",
            "thanks" => "Gracias por confiar en PasuNashi."
        ],
        "en" => [
            "subject" => "Authentication Code PasuNashi",
            "greeting" => "Hello!",
            "anti_fishing" => "This is an official PasuNashi email and this is the proof: ",
            "code" => "Your Authentication Code is: ",
            "thanks" => "Thank you for trusting in PsauSewa"
        ],
        "jp" => [
            "subject" => "パスなしからの認証コード",
            "greeting" => "こんにちは!",
            "anti_fishing" => "これはパスなしからの公式メールであり、その proof は次のとおりです。",
            "code" => "認証コードは次のとおりです。",
            "thanks" => "パスなしを信頼していただきありがとうございます。"
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
            ->subject($this->translations[$this->lang]["subject"])
            ->greeting($this->translations[$this->lang]["greeting"])
            ->line($this->translations[$this->lang]["anti_fishing"] . $this->secretAntiFishing)
            ->line($this->translations[$this->lang]["code"] . $this->code)
            ->salutation($this->translations[$this->lang]["thanks"]);
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
