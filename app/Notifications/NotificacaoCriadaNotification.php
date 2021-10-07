<?php

namespace App\Notifications;

use App\Models\Notificacao;
use App\Models\Requerimento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class NotificacaoCriadaNotification extends Notification
{
    use Queueable;
    private $notificacao;
    private $requerimento;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Notificacao $notificacao, Requerimento $requerimento)
    {
        $this->notificacao = $notificacao;
        $this->requerimento = $requerimento;
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
        $message = (new MailMessage)
            ->markdown(
                'mail.notificacao_criada',
                [
                    'requerimento' => $this->requerimento->tipoString(),
                    'empresa' => $this->requerimento->empresa->nome,
                    'imagens' => !$this->notificacao->fotos->isEmpty(),
                    'texto' => $this->notificacao->texto,
                ]
            )->subject('Notificação da Secretária de Meio Ambiente');
        foreach ($this->notificacao->fotos as $index => $foto) {
            $message->attach('storage/' . $foto->caminho, ['as' => 'foto' . $index + 1, 'mime' => 'image/png']);
        }
        return $message;
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
