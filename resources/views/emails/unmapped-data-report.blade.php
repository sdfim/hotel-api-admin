<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        h1, h2, h3 {
            margin: 0 0 8px 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: left;
            font-size: 15px;
        }
        .summary-table th {
            background-color: #e9ecef;
        }
        .no-data {
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daily Report: Unmapped Rooms and Rates</h1>
            <p>Date: {{ $date }}</p>
        </div>

        @if(empty($unmappedData))
            <p class="no-data">No unmapped data found for today.</p>
        @else
            <h2>Summary</h2>
            <table class="summary-table">
                <tr>
                    <th>Total Hotels with Unmapped Data</th>
                    <th>Total Unmapped Rooms</th>
                    <th>Total Unmapped Rates</th>
                </tr>
                <tr>
                    <td>{{ $summary['hotels'] }}</td>
                    <td>{{ $summary['rooms'] }}</td>
                    <td>{{ $summary['rates'] }}</td>
                </tr>
            </table>
            <p>The full detailed report is attached as a CSV file.</p>
        @endif
    </div>
</body>
</html> 