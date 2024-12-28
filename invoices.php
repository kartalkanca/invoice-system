<?php
// Fatura hesaplama ve veritabanı işlemleri için gerekli fonksiyonları dahil et
include 'invoice_operations.php'; 

// Siparişleri al
$orders = getOrders();

// Faturaları hesapla
$invoices = calculateInvoices($orders);

// En fazla harcama yapan müşteriyi bul
$topCustomer = getTopCustomer($invoices); // ID ve toplam harcamayı al

// HTML Görünümü
echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fatura Sistemi</title>
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
            flex-direction: column; /* Yatayda değil, dikeyde düzenleme yapıyoruz */
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
            color: #2ecc71; /* Yeşil renk */
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
            align-self: flex-start; /* Butonu sola hizalamak için */
        }
        .button:hover {
            background-color: #2980b9;
        }

        /* Üstteki tabloya sınırlı yükseklik */
        table {
            max-height: 300px; /* Üstteki tablonun boyutunu sınırlıyoruz */
            overflow-y: auto; /* Tablo yüksekliğini aştığında kaydırma çubuğu eklenir */
        }

        /* Alt kısmı iki farklı genişlikte parçalara ayırma */
        .bottom-section {
            display: flex;
            width: 100%; /* bottom-section genişliği %100 yapıldı */
            flex-wrap: wrap; /* Kolonların taşmasını engellemek için wrap özelliği eklendi */
        }

        .bottom-column {
            box-sizing: border-box; /* Padding hesaplamaları box-sizing ile kontrol edilecek */
            max-height: 300px; /* Alt tablolara yükseklik sınırlaması */
            overflow-y: auto; /* Yüksekliği aştığında kaydırma çubuğu eklenir */
        }

        /* İlk kolon %60 genişlik, ikinci kolon %40 genişlik */
        .left-column {
            flex: 0 0 60%; /* İlk kolon %60 */
            padding-right: 10px; /* Sağ tarafa boşluk ekledik */
        }

        .right-column {
            flex: 0 0 40%; /* İkinci kolon %40 */
            padding-left: 10px; /* Sol tarafa boşluk ekledik */
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
        <h2>Müşteri Faturaları</h2>
        <table>
            <tr>
                <th>Müşteri ID</th>
                <th>Fatura Numarası</th>
                <th>Toplam Tutar</th>
                <th>İndirim Miktarı</th>
                <th>Ödenen Tutar</th>
            </tr>";

            // Faturaları listele
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

        // Sayfanın alt kısmını iki farklı genişlikte parçalara ayırma
        echo "<div class='bottom-section'>";

        // En iyi müşteri için sol kısım
        echo "<div class='bottom-column left-column'>
                <table>
                    <tr>
                        <th>En İyi Müşteri ID</th>
                        <th>Toplam Harcama</th>
                    </tr>
                    <tr>
                        <td>{$topCustomer['customer_id']}</td>
                        <td>" . number_format($topCustomer['total_spent'], 2) . " TL</td>
                    </tr>
                </table>
              </div>";

        // Müşterilerden elde edilen toplam kazanç için sağ kısım
        $totalRevenue = array_sum(array_column($invoices, 'total_amount')); // Toplam kazanç
        echo "<div class='bottom-column right-column'>
                <table>
                    <tr>
                        <th>Toplam Kazanç</th>
                    </tr>
                    <tr>
                        <td>" . number_format($totalRevenue, 2) . " TL</td>
                    </tr>
                </table>
              </div>";

        echo "</div>"; // bottom-section div sonu

        echo "<a href='invoices.php' class='button'>Geri Git</a>
    </div>
</body>
</html>";
?>