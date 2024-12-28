<?php
// Veritabanı bağlantısı
include 'db.php'; // Veritabanı bağlantısını dahil et

// Siparişleri veritabanından çekme
function getOrders() {
    global $pdo;
    $orders = [];
    $query = "SELECT * FROM orders WHERE order_date BETWEEN '2024-01-01' AND '2024-12-31'"; // Örnek tarih aralığı
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $orders;
}

// Müşteri bazında gruplayıp faturaları hesaplama
function calculateInvoices($orders) {
    $customers = [];
    
    foreach ($orders as $order) {
        $customerId = $order['customer_id'];
        if (!isset($customers[$customerId])) {
            $customers[$customerId] = [
                'total_amount_without_discount' => 0, // İndirimsiz toplam
                'total_amount' => 0, // İndirimli toplam
                'orders' => [],
                'discount' => 0,
                'last_order_date' => $order['order_date'], // İlk siparişin tarihini al
            ];
        }
        $customers[$customerId]['orders'][] = $order;
        $customers[$customerId]['total_amount_without_discount'] += $order['amount']; // İndirimsiz toplam tutar
        $customers[$customerId]['total_amount'] += $order['amount']; // İndirimli toplam tutar (ilk başta aynı)

        // En son sipariş tarihini güncelle
        if ($order['order_date'] > $customers[$customerId]['last_order_date']) {
            $customers[$customerId]['last_order_date'] = $order['order_date'];
        }
    }

    // İndirim uygulama ve toplam fatura tutarını güncelleme
    $invoices = [];
    foreach ($customers as $customerId => $customerData) {
        $totalAmountWithoutDiscount = $customerData['total_amount_without_discount'];
        $totalAmount = $customerData['total_amount'];
        $discount = 0;

        // Eğer toplam tutar 500 TL'nin üzerinde ise %10 indirim uygulanır
        if ($totalAmountWithoutDiscount > 500) {
            $discount = $totalAmountWithoutDiscount * 0.1;
            $totalAmount -= $discount; // İndirimli tutar
        }

        $invoices[] = [
            'customer_id' => $customerId,
            'invoice_number' => "FTR-{$customerId}-{$customerData['last_order_date']}",
            'total_amount_without_discount' => $totalAmountWithoutDiscount, // İndirimsiz toplam tutar
            'total_amount' => $totalAmount, // İndirimli toplam tutar
            'discount' => $discount,
        ];
    }

    return $invoices;
}

// En fazla alışveriş yapan müşteriyi bulma ve toplam harcamasını döndürme
function getTopCustomer($invoices) {
    $customerTotals = [];
    foreach ($invoices as $invoice) {
        $customerTotals[$invoice["customer_id"]] = 
            ($customerTotals[$invoice["customer_id"]] ?? 0) + $invoice["total_amount"];
    }
    
    // En fazla harcama yapan müşteri
    arsort($customerTotals);
    $topCustomerId = array_key_first($customerTotals);
    $topCustomerTotal = $customerTotals[$topCustomerId];

    return [
        'customer_id' => $topCustomerId,
        'total_spent' => round($topCustomerTotal, 2)
    ];
}
?>