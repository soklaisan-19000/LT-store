<?php
session_start();
include 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { die("Unauthorized"); }

$data = json_decode($_POST['data'], true);
if (!$data) { die("No data selected."); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print Invoices</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f0f0; margin: 0; padding: 0; }
        .invoice-page { 
            background: white; 
            width: 210mm; 
            min-height: 297mm; 
            padding: 20mm; 
            margin: 20px auto; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            page-break-after: always;
        }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #333; padding-bottom: 20px; }
        .logo-area h1 { margin: 0; color: #1e293b; font-size: 28px; }
        .info-area { text-align: right; }
        .customer-box { margin: 40px 0; padding: 20px; background: #f8fafc; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1e293b; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-row { font-size: 20px; font-weight: bold; text-align: right; padding-top: 20px; }
        .footer-note { position: absolute; bottom: 20mm; left: 20mm; font-size: 12px; color: #94a3b8; }
        
        @media print {
            body { background: none; }
            .invoice-page { margin: 0; box-shadow: none; border: none; }
            .no-print { display: none; }
        }
        .no-print-btn { 
            position: fixed; top: 20px; right: 20px; 
            background: #3b82f6; color: white; padding: 15px 30px; 
            border-radius: 50px; cursor: pointer; border: none; font-weight: bold; z-index: 9999;
        }
    </style>
</head>
<body>

<button class="no-print-btn no-print" onclick="window.print()">Click to Print All Invoices</button>

<?php 
foreach ($data as $item) {
    $name = mysqli_real_escape_string($conn, $item['name']);
    $contact = mysqli_real_escape_string($conn, $item['contact']);
    
    // Get all items for this specific customer
    $query = "SELECT * FROM orders WHERE customer_name='$name' AND contact='$contact'";
    $result = mysqli_query($conn, $query);
    $total = 0;
?>

<div class="invoice-page">
    <div class="header">
        <div class="logo-area">
            <h1>LT SMS STORE</h1>
            <p>Official Sales Invoice</p>
        </div>
        <div class="info-area">
            <p><strong>Date:</strong> <?php echo date('d M Y'); ?></p>
            <p><strong>Invoice #:</strong> <?php echo rand(1000, 9999); ?></p>
        </div>
    </div>

    <div class="customer-box">
        <p style="margin:0; text-transform: uppercase; font-size: 12px; color: #64748b;">Bill To:</p>
        <h2 style="margin: 5px 0;"><?php echo $name; ?></h2>
        <p style="margin:0; color: #475569;">Contact: <?php echo $contact; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Description</th>
                <th style="text-align: right;">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) { 
                $total += $row['price'];
            ?>
            <tr>
                <td>
                    <strong><?php echo $row['product_name']; ?></strong><br>
                    <small style="color:#64748b"><?php echo $row['description']; ?></small>
                </td>
                <td style="text-align: right;">$<?php echo number_format($row['price'], 2); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="total-row">
        Total Amount: $<?php echo number_format($total, 2); ?>
    </div>

    <div class="footer-note">
        Thank you for your business! This is a computer-generated invoice.
    </div>
</div>

<?php } ?>

</body>
</html>