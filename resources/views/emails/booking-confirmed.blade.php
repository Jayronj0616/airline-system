<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
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
            background-color: #4F46E5;
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
            background-color: #10B981;
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
            border-left: 4px solid #4F46E5;
        }
        .info-section h3 {
            margin-top: 0;
            color: #4F46E5;
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
        .passenger-list {
            list-style: none;
            padding: 0;
        }
        .passenger-list li {
            padding: 10px;
            background-color: white;
            margin: 5px 0;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            color: #10B981;
            text-align: right;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .important-info {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
        }
        .important-info h4 {
            margin-top: 0;
            color: #92400E;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✈️ Booking Confirmed!</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $booking->user->name }},</p>
        
        <p>Your booking has been confirmed successfully. Thank you for choosing our airline!</p>
        
        <div class="booking-ref">
            {{ $booking->booking_reference }}
        </div>
        
        <p style="text-align: center; color: #666;">Your Booking Reference</p>
        
        <!-- Flight Information -->
        <div class="info-section">
            <h3>Flight Details</h3>
            <div class="info-row">
                <span class="label">Flight Number:</span>
                <span>{{ $flight->flight_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">From:</span>
                <span>{{ $flight->origin }}</span>
            </div>
            <div class="info-row">
                <span class="label">To:</span>
                <span>{{ $flight->destination }}</span>
            </div>
            <div class="info-row">
                <span class="label">Departure:</span>
                <span>{{ $flight->departure_time->format('M d, Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Arrival:</span>
                <span>{{ $flight->arrival_time->format('M d, Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Class:</span>
                <span>{{ $fareClass->name }}</span>
            </div>
        </div>
        
        <!-- Passengers -->
        <div class="info-section">
            <h3>Passengers & Seats</h3>
            <ul class="passenger-list">
                @foreach($passengers as $passenger)
                <li>
                    <span><strong>{{ $passenger->full_name }}</strong></span>
                    <span>Seat {{ $passenger->seat->seat_number }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        
        <!-- Payment Summary -->
        <div class="info-section">
            <h3>Payment Summary</h3>
            <div class="info-row">
                <span class="label">{{ $fareClass->name }} × {{ $booking->seat_count }}</span>
                <span>₱{{ number_format($booking->locked_price * $booking->seat_count, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Taxes & Fees</span>
                <span>₱0.00</span>
            </div>
            <div class="total">
                Total Paid: ₱{{ number_format($booking->total_price, 2) }}
            </div>
        </div>
        
        <!-- Important Information -->
        <div class="important-info">
            <h4>⚠️ Important Information</h4>
            <ul>
                <li>Please arrive at the airport at least 2 hours before departure</li>
                <li>Bring a valid government-issued ID for check-in</li>
                <li>Check-in opens 24 hours before departure</li>
                <li>Keep this booking reference handy for check-in</li>
            </ul>
        </div>
        
        <p>If you have any questions, please contact our customer service.</p>
        
        <p>Have a safe flight!</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} Airline System. All rights reserved.</p>
    </div>
</body>
</html>
