<!-- resources/views/sales.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f8f8;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Sales Report</h2>
        @if ($dateRange['from'] && $dateRange['to'])
            <p>Period: {{ $dateRange['from'] }} - {{ $dateRange['to'] }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Status</th>
                <th>Payment Method</th>
                <th class="text-right">Items</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->order_id }}</td>
                    <td>{{ $transaction->created_at->format('d M Y') }}</td>
                    <td>{{ ucfirst($transaction->status) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($transaction->payment_type)) }}</td>
                    <td class="text-right">{{ $transaction->items->sum('quantity') }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>

    <div class="summary">
        <p><strong>Total Orders:</strong> {{ $totalOrders }}</p>
        <p><strong>Total Items Sold:</strong> {{ $total_items }}</p> <!-- Tambahkan jumlah item -->
        <p><strong>Total Amount:</strong> Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
    </div>    
</body>

</html>
