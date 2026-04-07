<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public User $user;
    public string $resetUrl;

    public function __construct(string $token, User $user, string $resetUrl)
    {
        $this->token = $token;
        $this->user = $user;
        $this->resetUrl = $resetUrl;
    }

    public function build(): self
    {
        return $this->subject('Dat lai mat khau PETSAIGON')
            ->view('emails.reset-password');
    }
}
