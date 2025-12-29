<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Unauthorized access.");
}

$ids = isset($_GET['ids']) ? mysqli_real_escape_string($conn, $_GET['ids']) : '';
if (empty($ids)) { die("No records selected."); }

$start_date = $_GET['start'] ?: 'All Time';
$end_date = $_GET['end'] ?: 'Today';

// Fetch specific selected orders
$query = "SELECT id, customer_name, contact, status, description, price, order_date 
          FROM orders WHERE id IN ($ids) ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report - LT-STORE</title>
    <link rel="shortcut icon" href="image/photo_2025-08-21_13-04-14.jpg" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body { font-family: 'Inter', sans-serif; padding: 40px; color: #1e293b; background-color: #fff; line-height: 1.5; }
        
        /* Header UI */
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1e293b; padding-bottom: 25px; margin-bottom: 30px; }
        .logo-section { display: flex; align-items: center; gap: 20px; }
        .logo-img { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; }
        .company-name h1 { margin: 0; font-size: 28px; letter-spacing: -0.5px; color: #1e293b; }
        .company-name p { margin: 0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 14px; }
        
        .meta-info { text-align: right; }
        .meta-info p { margin: 2px 0; font-size: 13px; color: #475569; }
        .meta-info strong { color: #1e293b; }

        /* Table UI */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; padding: 14px 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; letter-spacing: 0.5px; }
        td { padding: 14px 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
        
        /* Status Badges */
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-PENDING { background: #fef3c7; color: #92400e; }
        .status-SHIPPED { background: #dbeafe; color: #1e40af; }
        .status-DELIVERED { background: #d1fae5; color: #065f46; }

        /* Footer & Total */
        .report-footer { display: flex; justify-content: flex-end; margin-top: 40px; }
        .total-card { background: #1e293b; color: white; padding: 20px 40px; border-radius: 12px; text-align: right; min-width: 250px; }
        .total-card span { display: block; font-size: 12px; text-transform: uppercase; opacity: 0.8; margin-bottom: 5px; font-weight: 600; }
        .total-card h2 { margin: 0; font-size: 32px; font-weight: 700; color: #fff; }

        .print-date { margin-top: 50px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 20px; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .total-card { border: 1px solid #1e293b; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 12px 24px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Print Sales Report</button>
    </div>

    <div class="report-header">
        <div class="logo-section">
            <img src="image/photo_2025-08-21_13-04-14.jpg" class="logo-img" alt="LT-STORE LOGO">
            <div class="company-name">
                <h1>LT-STORE</h1>
                <p>Official Sales Report</p>
            </div>
        </div>
        <div class="meta-info">
            <p><strong>Report ID:</strong> #SR-<?php echo date('mdHis'); ?></p>
            <p><strong>Period:</strong> <?php echo htmlspecialchars($start_date); ?> — <?php echo htmlspecialchars($end_date); ?></p>
            <p><strong>Exported:</strong> <?php echo date('d M Y, h:i A'); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="25%">Customer Details</th>
                <th width="35%">Order Description</th>
                <th width="13%">Status</th>
                <th width="15%" style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while($row = mysqli_fetch_assoc($result)): 
                $grand_total += $row['price'];
                $status = strtoupper($row['status'] ?: 'PENDING');
            ?>
            <tr>
                <td><strong><?php echo date('d M Y', strtotime($row['order_date'])); ?></strong></td>
                <td>
                    <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                    <div style="font-size: 12px; color: #64748b;"><?php echo htmlspecialchars($row['contact']); ?></div>
                </td>
                <td style="color: #475569; font-size: 12px;"><?php echo htmlspecialchars($row['description']); ?></td>
                <td><span class="badge status-<?php echo $status; ?>"><?php echo $status; ?></span></td>
                <td style="text-align: right; font-weight: 700; font-size: 15px; color: #1e293b;">
                    $<?php echo number_format($row['price'], 2); ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="report-footer">
        <div class="total-card">
            <span>Grand Total Revenue</span>
            <h2>$<?php echo number_format($grand_total, 2); ?></h2>
        </div>
    </div>

    <div class="print-date">
        This document is an official computer-generated sales report for LT-STORE. <br>
        &copy; <?php echo date('Y'); ?> LT-STORE. All rights reserved.
    </div>

</body>
</html>