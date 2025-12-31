<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .booking-ref {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .booking-ref-code {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 3px;
        }
        .flight-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .route {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
        .location {
            text-align: center;
        }
        .city {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .time {
            font-size: 18px;
            color: #667eea;
            margin: 5px 0;
        }
        .date {
            color: #666;
            font-size: 14px;
        }
        .arrow {
            font-size: 24px;
            color: #667eea;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .info-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-value {
            font-weight: bold;
            color: #333;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .passengers {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .passenger-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .passenger-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✈️ Booking Confirmed!</h1>
        <p>Your flight has been successfully booked</p>
    </div>

    <div class="content">
        <div class="booking-ref">
            <p style="margin: 0; color: #666;">Booking Reference</p>
            <div class="booking-ref-code">{{ $booking->booking_reference }}</div>
            <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">Please save this reference for future use</p>
        </div>

        <div class="flight-details">
            <h2 style="margin-top: 0;">Flight Details</h2>
            
            <div class="route">
                <div class="location">
                    <div class="city">{{ $booking->flight->origin }}</div>
                    <div class="time">{{ \Carbon\Carbon::parse($booking->flight->departure_time)->format('H:i') }}</div>
                    <div class="date">{{ \Carbon\Carbon::parse($booking->flight->departure_time)->format('M d, Y') }}</div>
                </div>
                <div class="arrow">→</div>
                <div class="location">
                    <div class="city">{{ $booking->flight->destination }}</div>
                    <div class="time">{{ \Carbon\Carbon::parse($booking->flight->arrival_time)->format('H:i') }}</div>
                    <div class="date">{{ \Carbon\Carbon::parse($booking->flight->arrival_time)->format('M d, Y') }}</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Flight Number</div>
                    <div class="info-value">{{ $booking->flight->flight_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Class</div>
                    <div class="info-value">{{ $booking->fareClass->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Passengers</div>
                    <div class="info-value">{{ $booking->seat_count }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Paid</div>
                    <div class="info-value">${{ number_format($booking->total_amount, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="passengers">
            <h3 style="margin-top: 0;">Passenger Information</h3>
            @foreach($booking->passengers as $passenger)
                <div class="passenger-item">
                    <strong>{{ $passenger->first_name }} {{ $passenger->last_name }}</strong>
                    @if($passenger->seat)
                        <span style="color: #667eea; margin-left: 10px;">Seat {{ $passenger->seat->seat_number }}</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="text-align: center;">
            <a href="{{ route('manage-booking.show', ['booking_reference' => $booking->booking_reference, 'last_name' => $booking->passengers->first()->last_name]) }}" class="button">
                Manage Booking
            </a>
        </div>

        <div style="background: #fff3cd; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107;">
            <strong>⚠️ Important Reminders:</strong>
            <ul style="margin: 10px 0;">
                <li>Online check-in opens 24 hours before departure</li>
                <li>Arrive at the airport at least 3 hours before departure</li>
                <li>Bring a valid ID and this booking reference</li>
            </ul>
        </div>

        <div class="footer">
            <p>Thank you for choosing our airline!</p>
            <p>For assistance, contact our support team or visit our website.</p>
            <p style="margin-top: 15px;">&copy; {{ date('Y') }} Airline System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
