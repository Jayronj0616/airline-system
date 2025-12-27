<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancelled</title>
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
            background-color: #DC2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .booking-ref {
            background-color: #6B7280;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-section {
            margin: 20px 0;
            padding: 15px;
            background-color: white;
            border-left: 4px solid #DC2626;
        }
        .info-section h3 {
            margin-top: 0;
            color: #DC2626;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .reason-box {
            background-color: #FEE2E2;
            border-left: 4px solid #DC2626;
            padding: 15px;
            margin: 20px 0;
        }
        .reason-box h4 {
            margin-top: 0;
            color: #991B1B;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .refund-info {
            background-color: #DBEAFE;
            border-left: 4px solid #3B82F6;
            padding: 15px;
            margin: 20px 0;
        }
        .refund-info h4 {
            margin-top: 0;
            color: #1E40AF;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>❌ Booking Cancelled</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $booking->user->name }},</p>
        
        <p>This email confirms that your booking has been cancelled.</p>
        
        <div class="booking-ref">
            {{ $booking->booking_reference }}
        </div>
        
        <p style="text-align: center; color: #666;">Cancelled Booking Reference</p>
        
        @if($reason)
        <div class="reason-box">
            <h4>Cancellation Reason</h4>
            <p>{{ $reason }}</p>
        </div>
        @endif
        
        <!-- Flight Information -->
        <div class="info-section">
            <h3>Cancelled Flight Details</h3>
            <div class="info-row">
                <span class="label">Flight Number:</span>
                <span>{{ $flight->flight_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Route:</span>
                <span>{{ $flight->origin }} → {{ $flight->destination }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <span>{{ $flight->departure_time->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Departure Time:</span>
                <span>{{ $flight->departure_time->format('h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Class:</span>
                <span>{{ $fareClass->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Passengers:</span>
                <span>{{ $booking->seat_count }}</span>
            </div>
            <div class="info-row">
                <span class="label">Cancelled On:</span>
                <span>{{ $booking->cancelled_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
        
        <!-- Refund Information -->
        <div class="refund-info">
            <h4>💰 Refund Information</h4>
            <p><strong>Total Amount:</strong> ₱{{ number_format($booking->total_price, 2) }}</p>
            <p>Your refund will be processed according to our cancellation policy. Depending on the fare rules and timing of cancellation, you may be eligible for a full or partial refund.</p>
            <p>Refunds are typically processed within 7-14 business days and will be credited to your original payment method.</p>
        </div>
        
        <p>If you have any questions about this cancellation or your refund, please contact our customer service team.</p>
        
        <p>We hope to serve you again in the future!</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} Airline System. All rights reserved.</p>
    </div>
</body>
</html>
