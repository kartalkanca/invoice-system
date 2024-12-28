<?php
// Include functions required for invoice calculation and database operations
include 'invoice_operations.php'; 

// Receive orders
$orders = getOrders();

// Calculate bills
$invoices = calculateInvoices($orders);

// Find the highest spending customer
$topCustomer = getTopCustomer($invoices); // Get ID and total spend

// HTML
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Invoice System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            width: 80%;
            max-width: 1200px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        h3 {
            font-size: 20px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th, table td {
            padding: 15px;
            text-align: left;
        }
        table th {
            background-color: #4CAF50;
            color: white;
        }
        table td {
            background-color: #f9f9f9;
        }
        table tr:nth-child(even) td {
            background-color: #f1f1f1;
        }
        table tr:hover td {
            background-color: #e1f7d5;
        }
        .discount {
            color: #e74c3c;
            font-weight: bold;
        }
        .paid-amount {
            color: #2ecc71;
            font-weight: bold;
        }
        .button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            align-self: flex-start;
        }
        .button:hover {
            background-color: #2980b9;
        }

        table {
            max-height: 300px;
            overflow-y: auto;
        }

        .bottom-section {
            display: flex;
            width: 100%;
            flex-wrap: wrap;
        }

        .bottom-column {
            box-sizing: border-box;
            max-height: 300px;
            overflow-y: auto;
        }

        .left-column {
            flex: 0 0 60%;
            padding-right: 10px;
        }

        .right-column {
            flex: 0 0 40%;
            padding-left: 10px;
        }

        .bottom-column h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #333;
        }

        .bottom-column table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .bottom-column th, .bottom-column td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .bottom-column th {
            background-color: #3498db;
            color: white;
        }
        .bottom-column td {
            background-color: #f9f9f9;
        }
        .bottom-column tr:nth-child(even) td {
            background-color: #f1f1f1;
        }

    </style>
</head>
<body>
    <div class='container'>
        <h2>Customer Invoices</h2>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>Invoice Number</th>
                <th>Total Amount</th>
                <th>Discount Amount</th>
                <th>Amount Paid</th>
            </tr>";

            // List invoices
            foreach ($invoices as $invoice) {
                echo "<tr>
                    <td>{$invoice['customer_id']}</td>
                    <td>{$invoice['invoice_number']}</td>
                    <td>" . number_format($invoice['total_amount_without_discount'], 2) . " TL</td>
                    <td class='discount'>" . ($invoice['discount'] > 0 ? number_format($invoice['discount'], 2) . " TL" : '0.00 TL') . "</td>
                    <td class='paid-amount'>" . number_format($invoice['total_amount'], 2) . " TL</td>
                </tr>";
            }

        echo "</table>";

        // Splitting the bottom of the page into two different widths
        echo "<div class='bottom-section'>";

        // Left side for best customer
        echo "<div class='bottom-column left-column'>
                <table>
                    <tr>
                        <th>Best Customer ID</th>
                        <th>Total Expenditure</th>
                    </tr>
                    <tr>
                        <td>{$topCustomer['customer_id']}</td>
                        <td>" . number_format($topCustomer['total_spent'], 2) . " TL</td>
                    </tr>
                </table>
              </div>";

        // Right side for total revenue from customers
        $totalRevenue = array_sum(array_column($invoices, 'total_amount')); // Total earnings
        echo "<div class='bottom-column right-column'>
                <table>
                    <tr>
                        <th>Total Earnings</th>
                    </tr>
                    <tr>
                        <td>" . number_format($totalRevenue, 2) . " TL</td>
                    </tr>
                </table>
              </div>";

        echo "</div>"; // bottom-section div end

        echo "<a href='invoices.php' class='button'>Go Back</a>
    </div>
</body>
</html>";
?>
