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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .cta-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .button {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .flight-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Check-in Now Open!</h1>
        <p>Your flight is ready for online check-in</p>
    </div>

    <div class="content">
        <div class="cta-box">
            <h2 style="margin-top: 0; color: #10b981;">⏰ Time to Check-in</h2>
            <p>Online check-in is now open for your flight {{ $booking->flight->flight_number }}</p>
            <p style="font-size: 18px; margin: 20px 0;">
                <strong>{{ $booking->flight->origin }}</strong> → <strong>{{ $booking->flight->destination }}</strong>
            </p>
            <p style="color: #666;">
                Departure: {{ \Carbon\Carbon::parse($booking->flight->departure_time)->format('M d, Y - H:i') }}
            </p>
            <a href="{{ route('manage-booking.check-in', ['booking_reference' => $booking->booking_reference, 'last_name' => $booking->passengers->first()->last_name]) }}" class="button">
                Check-in Now
            </a>
        </div>

        <div class="flight-info">
            <h3 style="margin-top: 0;">Booking Details</h3>
            <p><strong>Booking Reference:</strong> {{ $booking->booking_reference }}</p>
            <p><strong>Flight:</strong> {{ $booking->flight->flight_number }}</p>
            <p><strong>Passengers:</strong> {{ $booking->seat_count }}</p>
        </div>

        <div style="background: #dbeafe; padding: 15px; border-radius: 6px; border-left: 4px solid #3b82f6;">
            <strong>💡 Check-in Benefits:</strong>
            <ul style="margin: 10px 0;">
                <li>Choose or confirm your seat</li>
                <li>Get your boarding pass</li>
                <li>Save time at the airport</li>
                <li>Check-in closes 90 minutes before departure</li>
            </ul>
        </div>

        <div class="footer">
            <p>Have a safe flight!</p>
            <p>&copy; {{ date('Y') }} Airline System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
