<?php
session_start();
include 'db.php';

// Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Unauthorized access.");
}

if (!isset($_GET['name']) || !isset($_GET['contact'])) {
    die("Order details not specified.");
}

$name = mysqli_real_escape_string($conn, $_GET['name']);
$contact = mysqli_real_escape_string($conn, $_GET['contact']);

// Fetch all items for this specific customer
$query = "SELECT * FROM orders WHERE customer_name = '$name' AND contact = '$contact' ORDER BY order_date DESC";
$res = mysqli_query($conn, $query);

// Get the first row for general info (Date, Address)
$first_order = mysqli_query($conn, "SELECT * FROM orders WHERE customer_name = '$name' AND contact = '$contact' LIMIT 1");
$info = mysqli_fetch_assoc($first_order);

if (!$info) { die("Order not found."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Order - <?php echo $name; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; color: #333; line-height: 1.6; padding: 40px; }
        .invoice-header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .company-info h1 { margin: 0; color: #1e293b; letter-spacing: 2px; }
        .customer-details { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-row { text-align: right; font-size: 1.2rem; font-weight: bold; }
        .print-btn { background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <div class="invoice-header">
        <div class="company-info">
            <h1>LT SMS</h1>
            <p>Order Fulfillment Sheet</p>
        </div>
        <div style="text-align: right;">
            <button class="print-btn" onclick="window.print()">Print This Page</button>
            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($info['order_date'])); ?></p>
        </div>
    </div>

    <div class="customer-details">
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> <?php echo $info['customer_name']; ?><br>
        <strong>Contact:</strong> <?php echo $info['contact']; ?><br>
        <strong>Address:</strong> <?php echo $info['address']; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Item</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while($item = mysqli_fetch_assoc($res)) { 
                $grand_total += $item['price'];
            ?>
            <tr>
                <td>
                    <strong><?php echo $item['product_name']; ?></strong><br>
                    <small style="color: #666;"><?php echo $item['description']; ?></small>
                </td>
                <td>$<?php echo number_format($item['price'], 2); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="total-row">
        Total Amount: $<?php echo number_format($grand_total, 2); ?>
    </div>

    <div style="margin-top: 50px; border-top: 1px dashed #ccc; padding-top: 20px; font-size: 0.8rem; text-align: center;">
        Thank you for choosing LT SMS. Please keep this for your records.
    </div>

</body>
</html>