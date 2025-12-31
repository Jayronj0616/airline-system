<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Cancelled - ' . $this->booking->booking_reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-cancellation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
