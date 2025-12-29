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
$fb_link = "https://facebook.com/LT-Store"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoices | LT-STORE</title>
    <link rel="shortcut icon" href="image/photo_2025-08-21_13-04-14.jpg" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; --text-muted: #64748b; --border: #e2e8f0; }
        
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 20px; background: #f1f5f9; color: var(--primary); }
        
        .invoice-card { 
            background: white; 
            width: 210mm;
            max-width: 800px;
            margin: 0 auto 30px auto; 
            padding: 40px; 
            border-radius: 12px; 
            position: relative; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            page-break-after: always; 
            box-sizing: border-box;
        }
        
        .invoice-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: var(--accent); border-radius: 12px 12px 0 0; }
        
        /* Updated Header for Logo */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .brand-container { display: flex; align-items: center; gap: 15px; }
        .main-logo { width: 70px; height: 70px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border); }
        
        .brand h1 { margin: 0; font-weight: 800; font-size: 26px; letter-spacing: -1px; line-height: 1; }
        .brand p { margin: 5px 0 0; font-size: 13px; color: var(--text-muted); font-weight: 600; }
        
        .invoice-meta { text-align: right; }
        .meta-label { color: var(--text-muted); font-size: 11px; font-weight: 800; text-transform: uppercase; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; padding-top: 20px; border-top: 1px solid var(--border); }
        .info-block h4 { text-transform: uppercase; font-size: 11px; color: var(--text-muted); margin-bottom: 6px; margin-top: 0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; padding: 12px; background: #f8fafc; font-size: 12px; border-bottom: 2px solid var(--border); color: var(--text-muted); text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }

        .item-cell { display: flex; align-items: center; gap: 12px; }
        .prod-img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); }
        
        .total-row { display: flex; justify-content: flex-end; align-items: center; gap: 20px; padding: 15px 0; }
        .total-label { font-weight: 700; color: var(--text-muted); font-size: 14px; }
        .total-amount { font-weight: 800; color: var(--accent); font-size: 24px; }

        .description-box { margin-bottom: 30px; padding: 15px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border); }
        .description-header { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block; }
        .description-content { font-size: 13px; line-height: 1.5; }

        .footer-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 20px; }
        .footer-left { display: flex; align-items: center; gap: 15px; }
        .qr-section img { width: 70px; height: 70px; border: 1px solid var(--border); padding: 2px; border-radius: 6px; background: white; }
        .qr-text { font-size: 10px; color: var(--text-muted); width: 80px; font-weight: 700; line-height: 1.2; text-transform: uppercase; }

        .social-bar { display: flex; flex-direction: column; gap: 5px; }
        .social-bar a { text-decoration: none; color: var(--primary); font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: flex-end; gap: 8px; }

        .thanks-msg { text-align: center; font-size: 11px; color: var(--text-muted); margin-top: 20px; }

        @media print { 
            @page { margin: 0; size: auto; }
            body { background: white; padding: 0; margin: 0; } 
            .no-print { display: none; } 
            .invoice-card { box-shadow: none; width: 100%; max-width: 100%; margin: 0; padding: 40px; border-radius: 0; } 
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:center; margin-bottom: 30px;">
        <button onclick="window.print()" style="padding: 14px 28px; background:var(--accent); color:white; border:none; border-radius:50px; cursor:pointer; font-weight:700; font-size:15px;">
            <i class="fas fa-print"></i> Click to Print All Invoices
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

        $desc_res = mysqli_query($conn, "SELECT description FROM orders WHERE customer_name = '$c_name_sql' AND contact = '$c_contact_sql' ORDER BY id DESC LIMIT 1");
        $desc_data = mysqli_fetch_assoc($desc_res);
        $single_description = $desc_data['description'] ?? '';
    ?>

    <div class="invoice-card">
        <div class="header">
            <div class="brand-container">
                <img src="image/photo_2025-08-21_13-04-14.jpg" alt="LT-STORE Logo" class="main-logo">
                <div class="brand">
                    <h1>LT-STORE</h1>
                    <p>Premium Digital Solutions</p>
                </div>
            </div>
            <div class="invoice-meta">
                <span class="meta-label">Reference</span>
                <h2 style="margin:2px 0 0; color:var(--accent); font-size: 20px; font-weight: 800;">#REF-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h2>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <h4>Customer:</h4>
                <p style="margin:0; font-size: 15px;"><strong><?php echo htmlspecialchars($customer_name); ?></strong></p>
                <p style="margin:2px 0 0; font-size: 13px; color: var(--text-muted);"><?php echo htmlspecialchars($contact); ?></p>
            </div>
            <div class="info-block" style="text-align:right;">
                <h4>Details:</h4>
                <p style="margin:0; font-size: 13px; font-weight: 600;"><?php echo date('d M, Y'); ?></p>
                <p style="margin:2px 0 0; font-size: 13px; color:var(--accent); font-weight:800;"><?php echo $status; ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Items Purchased</th>
                    <th style="text-align: right;">Price</th>
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
                    <td>
                        <div class="item-cell">
                            <img src="<?php echo $img_path; ?>" class="prod-img">
                            <span style="font-weight:700; font-size: 13px;"><?php echo htmlspecialchars($item['product_name']); ?></span>
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 700; font-size: 14px;">$<?php echo number_format($item['price'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="total-row">
            <span class="total-label">GRAND TOTAL:</span>
            <span class="total-amount">$<?php echo number_format($grand_total, 2); ?></span>
        </div>

        <?php if (!empty($single_description)): ?>
        <div class="description-box">
            <span class="description-header">Special Notes</span>
            <div class="description-content"><?php echo nl2br(htmlspecialchars($single_description)); ?></div>
        </div>
        <?php endif; ?>

        <div class="footer-row">
            <div class="footer-left">
                <div class="qr-section">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($tg_link); ?>" alt="QR">
                </div>
                <div class="qr-text">Scan for Telegram</div>
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