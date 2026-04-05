<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public User $user;
    public string $activationUrl;

    public function __construct(string $token, User $user, string $activationUrl)
    {
        $this->token = $token;
        $this->user = $user;
        $this->activationUrl = $activationUrl;
    }

    public function build(): self
    {
        return $this->subject('Kich hoat tai khoan PETSAIGON')
            ->view('emails.activation');
    }
}
