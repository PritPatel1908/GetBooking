<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $booking->booking_sku }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-title {
            font-size: 24px;
            color: #2B6CB0;
            margin-bottom: 10px;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details th, .invoice-details td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .invoice-details th {
            background-color: #f8f9fa;
        }
        .booking-details {
            margin-bottom: 30px;
        }
        .booking-details h3 {
            color: #2B6CB0;
            margin-bottom: 15px;
        }
        .total-amount {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1 class="invoice-title">Booking Invoice</h1>
        <p>Invoice #{{ $booking->booking_sku }}</p>
        <p>Date: {{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</p>
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <th>Customer Details</th>
                <th>Booking Details</th>
            </tr>
            <tr>
                <td>
                    <strong>Name:</strong> {{ $booking->user->name }}<br>
                    <strong>Email:</strong> {{ $booking->user->email }}<br>
                    <strong>Phone:</strong> {{ $booking->user->phone ?? 'N/A' }}
                </td>
                <td>
                    <strong>Booking Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}<br>
                    <strong>Time:</strong> {{ $booking->booking_time }}<br>
                    <strong>Duration:</strong> {{ $booking->duration }} hours
                </td>
            </tr>
        </table>
    </div>

    <div class="booking-details">
        <h3>Ground Details</h3>
        @if($booking->details->isNotEmpty() && $booking->details->first()->ground)
            @php
                $ground = $booking->details->first()->ground;
            @endphp
            <table>
                <tr>
                    <th>Ground Name</th>
                    <td>{{ $ground->name }}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>{{ $ground->location }}</td>
                </tr>
                <tr>
                    <th>Additional Services</th>
                    <td>
                        @if($ground->features->isNotEmpty())
                            {{ $ground->features->pluck('feature_name')->implode(', ') }}
                        @else
                            None
                        @endif
                    </td>
                </tr>
            </table>
        @endif
    </div>

    <div class="total-amount">
        Total Amount: ₹{{ number_format($booking->amount, 2) }}
    </div>

    <div class="footer">
        <p>Thank you for choosing our service!</p>
        <p>This is a computer-generated invoice, no signature required.</p>
    </div>
</body>
</html>
