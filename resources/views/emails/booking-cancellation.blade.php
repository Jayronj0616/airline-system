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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        .info-box {
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
        <h1>❌ Booking Cancelled</h1>
        <p>Your booking has been cancelled</p>
    </div>

    <div class="content">
        <div class="info-box">
            <h2 style="margin-top: 0;">Cancellation Confirmation</h2>
            <p>Your booking <strong>{{ $booking->booking_reference }}</strong> has been successfully cancelled.</p>
            
            <div style="margin: 20px 0; padding: 15px; background: #f3f4f6; border-radius: 6px;">
                <p style="margin: 5px 0;"><strong>Flight:</strong> {{ $booking->flight->flight_number }}</p>
                <p style="margin: 5px 0;"><strong>Route:</strong> {{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                <p style="margin: 5px 0;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->flight->departure_time)->format('M d, Y') }}</p>
                <p style="margin: 5px 0;"><strong>Amount:</strong> ${{ number_format($booking->total_amount, 2) }}</p>
            </div>

            @if($booking->fareClass->refundable)
                <div style="background: #dcfce7; padding: 15px; border-radius: 6px; border-left: 4px solid #10b981;">
                    <p style="margin: 0;"><strong>✓ Refund Eligible</strong></p>
                    <p style="margin: 10px 0 0 0;">Your refund will be processed within 5-7 business days.</p>
                </div>
            @else
                <div style="background: #fee2e2; padding: 15px; border-radius: 6px; border-left: 4px solid #ef4444;">
                    <p style="margin: 0;"><strong>⚠️ Non-Refundable</strong></p>
                    <p style="margin: 10px 0 0 0;">This fare class is non-refundable according to the fare rules.</p>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>We hope to serve you again soon!</p>
            <p>If you have any questions, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} Airline System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
