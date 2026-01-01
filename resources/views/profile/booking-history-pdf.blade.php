<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking History - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 5px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
        }
        .stat-label {
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking History</h1>
        <p>{{ $user->name }} ({{ $user->email }})</p>
        <p>Generated on {{ now()->format('F d, Y') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['total_flights'] }}</div>
            <div class="stat-label">Total Flights</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $statistics['countries_visited'] }}</div>
            <div class="stat-label">Countries</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($statistics['total_miles']) }}</div>
            <div class="stat-label">Miles</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">₱{{ number_format($statistics['total_spent']) }}</div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Flight</th>
                <th>Route</th>
                <th>Date</th>
                <th>Time</th>
                <th>Class</th>
                <th>Passengers</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pastTrips as $booking)
            <tr>
                <td>{{ $booking->booking_reference }}</td>
                <td>{{ $booking->flight->flight_number }}</td>
                <td>{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</td>
                <td>{{ $booking->flight->departure_time->format('M d, Y') }}</td>
                <td>{{ $booking->flight->departure_time->format('H:i') }}</td>
                <td>{{ $booking->fareClass->name }}</td>
                <td>{{ $booking->passengers->count() }}</td>
                <td>₱{{ number_format($booking->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This document was automatically generated. For questions, please contact support.</p>
        <p>Member since {{ $statistics['member_since']->format('F Y') }}</p>
    </div>
</body>
</html>
