<?php
include 'db.php';

// --- LOGIC TO HANDLE MULTIPLE OR SINGLE IDS ---
$ids_to_print = [];
if (isset($_GET['ids'])) {
    $ids_to_print = explode(',', $_GET['ids']);
} elseif (isset($_GET['id'])) {
    $ids_to_print[] = $_GET['id'];
} else {
    die("Order ID required");
}

$tg_link = "https://t.me/ltstore168"; 
$fb_link = "https://facebook.com/YOUR_PAGE"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoices | LTSTORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; --text-muted: #64748b; --border: #e2e8f0; }
        
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 20px; background: #f1f5f9; color: var(--primary); }
        
        /* Fixed width for invoice to ensure it fits A4 paper exactly */
        .invoice-card { 
            background: white; 
            width: 100%;
            max-width: 700px; /* Reduced width to prevent cutoff */
            margin: 0 auto 30px auto; 
            padding: 30px; 
            border-radius: 12px; 
            position: relative; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            height: auto; 
            page-break-after: always; 
            box-sizing: border-box; /* Crucial for layout padding */
        }
        
        .invoice-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: var(--accent); border-radius: 12px 12px 0 0; }
        
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
        .brand h1 { margin: 0; font-weight: 800; font-size: 22px; letter-spacing: -1px; }
        .invoice-meta { text-align: right; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; padding-top: 15px; border-top: 1px solid var(--border); }
        .info-block h4 { text-transform: uppercase; font-size: 10px; color: var(--text-muted); margin-bottom: 5px; margin-top: 0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        th { text-align: left; padding: 10px; background: #f8fafc; font-size: 11px; border-bottom: 2px solid var(--border); color: var(--text-muted); }
        td { padding: 8px 10px; border-bottom: 1px solid var(--border); vertical-align: middle; word-wrap: break-word; }

        .prod-img { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; margin-right: 10px; border: 1px solid var(--border); flex-shrink: 0; }
        
        .description-box { margin-bottom: 15px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid var(--border); }
        .description-header { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 4px; }
        .description-content { font-size: 12px; color: var(--primary); line-height: 1.4; }

        /* Compact Footer Row */
        .footer-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-top: 15px; 
            padding-top: 15px; 
            border-top: 1px solid var(--border); 
        }

        .footer-left { display: flex; align-items: center; gap: 12px; }
        .qr-section img { width: 60px; height: 60px; border: 1px solid var(--border); padding: 2px; border-radius: 4px; }
        .qr-text { font-size: 9px; color: var(--text-muted); width: 70px; font-weight: 700; line-height: 1.1; }

        .social-bar { display: flex; flex-direction: column; gap: 5px; text-align: right; }
        .social-bar a { text-decoration: none; color: var(--primary); font-size: 12px; font-weight: 600; display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

        .thanks-msg { text-align: center; font-size: 9px; color: var(--text-muted); margin-top: 15px; }

        /* PRINT SETTINGS */
        @media print { 
            @page { margin: 0; size: auto; } /* Removes browser headers/footers */
            .no-print { display: none; } 
            body { background: white; padding: 0; margin: 0; } 
            .invoice-card { 
                box-shadow: none; 
                border: none; 
                width: 100%; 
                max-width: 100%; 
                margin: 0; 
                padding: 40px; 
                border-radius: 0;
            } 
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 12px 24px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px;">
            <i class="fas fa-print"></i> PRINT INVOICES
        </button>
    </div>

    <?php 
    foreach ($ids_to_print as $current_id):
        $safe_id = mysqli_real_escape_string($conn, trim($current_id));
        $res = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$safe_id'");
        $order = mysqli_fetch_assoc($res);
        if (!$order) continue; 

        $customer_name = $order['customer_name'];
        $contact = $order['contact'];
        $status = strtoupper($order['status'] ?? 'PENDING');

        $c_name_sql = mysqli_real_escape_string($conn, $customer_name);
        $c_contact_sql = mysqli_real_escape_string($conn, $contact);
        $all_items = mysqli_query($conn, "SELECT * FROM orders WHERE customer_name = '$c_name_sql' AND contact = '$c_contact_sql'");

        $desc_res = mysqli_query($conn, "SELECT description FROM orders WHERE customer_name = '$c_name_sql' AND contact = '$c_contact_sql' LIMIT 1");
        $desc_data = mysqli_fetch_assoc($desc_res);
        $single_description = $desc_data['description'] ?? '';
    ?>

    <div class="invoice-card">
        <div class="header">
            <div class="brand">
                <h1>LT-STORE</h1>
                <p style="margin:2px 0; font-size: 11px; color:var(--text-muted);">Premium Digital Solutions</p>
            </div>
            <div class="invoice-meta">
                <p style="margin:0; font-size: 9px; font-weight:800; color:var(--text-muted);">REFERENCE</p>
                <h2 style="margin:0; color:var(--accent); font-size: 16px;">#REF-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h2>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <h4>Customer:</h4>
                <p style="margin:0; font-size: 13px;"><strong><?php echo htmlspecialchars($customer_name); ?></strong></p>
                <p style="margin:0; font-size: 12px;"><?php echo htmlspecialchars($contact); ?></p>
            </div>
            <div class="info-block" style="text-align:right;">
                <h4>Details:</h4>
                <p style="margin:0; font-size: 12px;"><?php echo date('d M, Y'); ?></p>
                <p style="margin:0; font-size: 12px; color:var(--accent); font-weight:800;"><?php echo $status; ?></p>
            </div>
        </div>

        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 75%;">Items Purchased</th>
                    <th style="width: 25%; text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total = 0;
                while($item = mysqli_fetch_assoc($all_items)) { 
                    $grand_total += $item['price'];
                    $img_name = !empty($item['product_image']) ? $item['product_image'] : 'default.jpg';
                    $img_path = "uploads/".$img_name;
                ?>
                <tr>
                    <td style="display:flex; align-items:center; border-bottom: none;">
                        <img src="<?php echo $img_path; ?>" class="prod-img">
                        <div style="font-weight:600; font-size: 13px; line-height:1.2;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    </td>
                    <td style="text-align: right; font-weight: 700; font-size: 13px;">$<?php echo number_format($item['price'], 2); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td style="text-align: right; font-weight: 800; font-size: 11px; color: var(--text-muted); border:none;">GRAND TOTAL:</td>
                    <td style="text-align: right; font-weight: 800; font-size: 16px; color: var(--accent); border:none; padding-top:15px;">$<?php echo number_format($grand_total, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($single_description)): ?>
        <div class="description-box">
            <span class="description-header">Special Notes</span>
            <div class="description-content"><?php echo nl2br(htmlspecialchars($single_description)); ?></div>
        </div>
        <?php endif; ?>

        <div class="footer-row">
            <div class="footer-left">
                <div class="qr-section">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($tg_link); ?>" alt="QR">
                </div>
                <div class="qr-text">SCAN FOR TELEGRAM</div>
            </div>

            <div class="social-bar">
                <a href="<?php echo $tg_link; ?>"><i class="fab fa-telegram" style="color:#0088cc;"></i> @ltstore168</a>
                <a href="<?php echo $fb_link; ?>"><i class="fab fa-facebook" style="color:#1877f2;"></i> LT-Store</a>
            </div>
        </div>
        
        <p class="thanks-msg">Thank you for your business! Follow us for updates.</p>
    </div>

    <?php endforeach; ?>

</body>
</html>