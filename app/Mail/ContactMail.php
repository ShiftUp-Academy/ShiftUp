<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nom;
    public $prenom;
    public $sujet;
    public $email;
    public $messageBody;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->nom = $data['nom'];
        $this->prenom = $data['prenom'];
        $this->sujet = $data['sujet'];
        $this->email = $data['email'];
        $this->messageBody = $data['message'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Contact-ShiftUp] " . $this->sujet,
            replyTo: [$this->email]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
