<?php
include 'db.php'; // Include database connection

// Retrieve orders from database
function getOrders() {
    global $pdo;
    $orders = [];
    $query = "SELECT * FROM orders WHERE order_date BETWEEN '2024-01-01' AND '2024-12-31'"; // Sample date range
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $orders;
}

// Grouping and calculating invoices based on customers
function calculateInvoices($orders) {
    $customers = [];
    
    foreach ($orders as $order) {
        $customerId = $order['customer_id'];
        if (!isset($customers[$customerId])) {
            $customers[$customerId] = [
                'total_amount_without_discount' => 0, // Total without discount
                'total_amount' => 0, // Discounted total
                'orders' => [],
                'discount' => 0,
                'last_order_date' => $order['order_date'], // First order
            ];
        }
        $customers[$customerId]['orders'][] = $order;
        $customers[$customerId]['total_amount_without_discount'] += $order['amount']; // Total amount without discount
        $customers[$customerId]['total_amount'] += $order['amount']; // Total amount discounted (same as at first)

        // Update latest order date
        if ($order['order_date'] > $customers[$customerId]['last_order_date']) {
            $customers[$customerId]['last_order_date'] = $order['order_date'];
        }
    }

    // Apply discounts and update total invoice amount
    $invoices = [];
    foreach ($customers as $customerId => $customerData) {
        $totalAmountWithoutDiscount = $customerData['total_amount_without_discount'];
        $totalAmount = $customerData['total_amount'];
        $discount = 0;

        // If the total amount is over 500 TL, a 10% discount is applied
        if ($totalAmountWithoutDiscount > 500) {
            $discount = $totalAmountWithoutDiscount * 0.1;
            $totalAmount -= $discount; // Discounted amount
        }

        $invoices[] = [
            'customer_id' => $customerId,
            'invoice_number' => "FTR-{$customerId}-{$customerData['last_order_date']}",
            'total_amount_without_discount' => $totalAmountWithoutDiscount, // Total amount without discount
            'total_amount' => $totalAmount, // Total discounted amount
            'discount' => $discount,
        ];
    }

    return $invoices;
}

// Find the customer who made the most purchases
function getTopCustomer($invoices) {
    $customerTotals = [];
    foreach ($invoices as $invoice) {
        $customerTotals[$invoice["customer_id"]] = 
            ($customerTotals[$invoice["customer_id"]] ?? 0) + $invoice["total_amount"];
    }
    
    // Highest spending customer
    arsort($customerTotals);
    $topCustomerId = array_key_first($customerTotals);
    $topCustomerTotal = $customerTotals[$topCustomerId];

    return [
        'customer_id' => $topCustomerId,
        'total_spent' => round($topCustomerTotal, 2)
    ];
}
?>
