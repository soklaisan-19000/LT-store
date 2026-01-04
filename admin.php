<?php
session_start();
include 'db.php';

// Ensure products table has stock_status column
$col_res = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock_status'");
if (mysqli_num_rows($col_res) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN stock_status VARCHAR(20) NOT NULL DEFAULT 'In Stock'");
}
// Ensure products table has stock_qty column
$col_res2 = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock_qty'");
if (mysqli_num_rows($col_res2) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN stock_qty INT NOT NULL DEFAULT 0");
}
// Ensure orders table has quantity column
$col_res3 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'quantity'");
if (mysqli_num_rows($col_res3) == 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN quantity INT NOT NULL DEFAULT 1");
}

// Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// --- DATE FILTER LOGIC ---
$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : '';

$date_query = "";
if (!empty($start_date) && !empty($end_date)) {
    $date_query = " WHERE order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
}

// --- PHP LOGIC ---
if (isset($_POST['update_status'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $c_contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $res = mysqli_query($conn, "UPDATE orders SET status='$new_status' WHERE customer_name='$c_name' AND contact='$c_contact'");
    // If this is an AJAX request, return JSON so the page doesn't need to reload
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => (bool)$res, 'status' => $new_status));
        exit();
    }
    if ($res) {
        header("Location: admin.php?msg=status_updated");
    } else {
        header("Location: admin.php?msg=error");
    }
    exit();
}

if (isset($_GET['delete_customer_orders'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $contact = mysqli_real_escape_string($conn, $_GET['contact']);
    mysqli_query($conn, "DELETE FROM orders WHERE customer_name='$name' AND contact='$contact'");
    header("Location: admin.php?msg=order_deleted");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $res = mysqli_query($conn, "SELECT image FROM products WHERE id='$id'");
    $p = mysqli_fetch_assoc($res);
    if($p && $p['image'] != 'default.jpg') { @unlink("uploads/".$p['image']); }
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    header("Location: admin.php?msg=deleted");
    exit();
}

// AJAX/GET: return all orders for a specific customer (used by admin modal view)
if (isset($_GET['view_orders'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $contact = mysqli_real_escape_string($conn, $_GET['contact']);
    $start = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : '';
    $end = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : '';
    $date_cond = '';
    if (!empty($start) && !empty($end)) {
        $date_cond = " AND order_date BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
    }
    $res_orders = mysqli_query($conn, "SELECT * FROM orders WHERE customer_name='$name' AND contact='$contact' $date_cond ORDER BY id DESC");
    echo '<div style="max-height:60vh; overflow:auto; padding:6px 0;">';
    echo '<div style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:6px 0 12px;">Orders for '.htmlspecialchars($name).' ('.htmlspecialchars($contact).')</h3>
            <div>
                <button onclick="printView()" style="background:#2563eb; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; margin-right:8px;">Print</button>
                <button onclick="closeView()" style="background:#ddd; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;">Close</button>
            </div>
          </div>';
    if (mysqli_num_rows($res_orders) == 0) {
        echo '<div style="padding:12px; color:#64748b;">No orders found for this customer in the selected range.</div>';
    } else {
        echo '<table style="width:100%; border-collapse:collapse; font-size:0.95rem;"><thead><tr style="background:#f7fafc; color:#334155;">
                <th style="padding:8px; text-align:left;">ID</th>
                <th style="padding:8px; text-align:left;">Product</th>
                <th style="padding:8px;">Qty</th>
                <th style="padding:8px; text-align:right;">Unit Price</th>
                <th style="padding:8px; text-align:right;">Total</th>
                <th style="padding:8px;">Status</th>
                <th style="padding:8px;">Address</th>
                <th style="padding:8px;">Date</th>
            </tr></thead><tbody>';
        $sum_qty = 0; $sum_total = 0.0;
        while($r = mysqli_fetch_assoc($res_orders)) {
            $order_id = intval($r['id']);
            $prod = htmlspecialchars($r['product_name'] ?? $r['description']);
            $qty = intval($r['quantity'] ?? 1);
            $total = floatval($r['price']);
            $unit = $qty > 0 ? $total / $qty : $total;
            $status = htmlspecialchars(strtoupper($r['status'] ?? 'PENDING'));
            $addr = htmlspecialchars($r['address'] ?? '');
            $img = !empty($r['product_image']) ? $r['product_image'] : 'default.jpg';
            echo '<tr style="border-bottom:1px solid #eef2f7;">';
            echo '<td style="padding:10px; vertical-align:middle;">'.$order_id.'</td>';
            echo '<td style="padding:10px;">'.'<div style="display:flex; align-items:center; gap:8px;"><img src="uploads/'.htmlspecialchars($img).'" style="width:48px; height:48px; object-fit:cover; border-radius:6px;">'.'<div>'.nl2br($prod).'</div></div>'.'</td>';
            echo '<td style="padding:10px; text-align:center;">'.$qty.'</td>';
            echo '<td style="padding:10px; text-align:right;">$'.number_format($unit,2).'</td>';
            echo '<td style="padding:10px; text-align:right;">$'.number_format($total,2).'</td>';
            echo '<td style="padding:10px;">'.$status.'</td>';
            echo '<td style="padding:10px;">'.(strlen($addr) > 60 ? substr($addr,0,60).'...' : $addr).'</td>';
            echo '<td style="padding:10px;">'.htmlspecialchars($r['order_date']).'</td>';
            echo '</tr>';
            $sum_qty += $qty; $sum_total += $total;
        }
        echo '<tr style="font-weight:700; background:#fbfdff;"><td colspan="2" style="padding:10px;">Summary</td><td style="padding:10px; text-align:center;">'.$sum_qty.'</td><td></td><td style="padding:10px; text-align:right;">$'.number_format($sum_total,2).'</td><td colspan="3"></td></tr>';
        echo '</tbody></table>';
    }
    echo '</div>';
    exit();
}

// POLL: return product stocks and dashboard totals for admin live updates
if (isset($_GET['poll_products'])) {
    $res_all = mysqli_query($conn, "SELECT id, stock_qty, stock_status FROM products");
    $products = array();
    while($pp = mysqli_fetch_assoc($res_all)) {
        $products[] = array('id' => intval($pp['id']), 'stock_qty' => intval($pp['stock_qty']), 'stock_status' => $pp['stock_status']);
    }
    $items_res = mysqli_query($conn, "SELECT COALESCE(SUM(stock_qty),0) AS total_items FROM products");
    $items_row = mysqli_fetch_assoc($items_res);
    $total_stock_items = intval($items_row['total_items'] ?? 0);
    $total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
    header('Content-Type: application/json');
    echo json_encode(array('success' => true, 'total_stock_items' => $total_stock_items, 'total_products' => $total_products, 'products' => $products));
    exit();
}

if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $stock_qty = intval($_POST['stock_qty'] ?? 0);
    $stock_status = ($stock_qty <= 0) ? 'Out of Stock' : 'In Stock';
    $imageName = $_FILES['image']['name'];
    if(!empty($imageName)) {
        $uniqueName = time() . "_" . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $uniqueName);
    } else { $uniqueName = "default.jpg"; }
    mysqli_query($conn, "INSERT INTO products (name, price, description, image, stock_status, stock_qty) VALUES ('$name', '$price', '$desc', '$uniqueName', '$stock_status', '$stock_qty')");
    header("Location: admin.php?msg=added");
    exit();
} 

if (isset($_POST['update_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $stock_qty = intval($_POST['stock_qty'] ?? 0);
    $stock_status = ($stock_qty <= 0) ? 'Out of Stock' : 'In Stock';
    if(!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
        $img_query = ", image='$imageName'";
    } else { $img_query = ""; }
    mysqli_query($conn, "UPDATE products SET name='$name', price='$price', description='$desc', stock_status='$stock_status', stock_qty='$stock_qty' $img_query WHERE id='$id'");
    header("Location: admin.php?msg=updated");
    exit();
} 

// AJAX: update stock status or quantity inline
if (isset($_POST['update_stock'])) {
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $response = array('success' => false);
    if (isset($_POST['stock_qty'])) {
        $stock_qty = intval($_POST['stock_qty']);
        $stock_status = ($stock_qty <= 0) ? 'Out of Stock' : 'In Stock';
        $res = mysqli_query($conn, "UPDATE products SET stock_qty='$stock_qty', stock_status='$stock_status' WHERE id='$id'");
        // Recompute total stock items for dashboard
        $items_res = mysqli_query($conn, "SELECT COALESCE(SUM(stock_qty),0) AS total_items FROM products");
        $items_row = mysqli_fetch_assoc($items_res);
        $total_stock_items = intval($items_row['total_items'] ?? 0);
        $response = array('success' => (bool)$res, 'stock_qty' => $stock_qty, 'stock_status' => $stock_status, 'total_stock_items' => $total_stock_items);
    }
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    if (!empty($response['success'])) {
        header("Location: admin.php?msg=updated");
    } else {
        header("Location: admin.php?msg=error");
    }
    exit();
}




// DASHBOARD STATS
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
// total number of individual items available (sum of stock_qty)
$items_res = mysqli_query($conn, "SELECT COALESCE(SUM(stock_qty),0) AS total_items FROM products");
$items_row = mysqli_fetch_assoc($items_res);
$total_stock_items = intval($items_row['total_items'] ?? 0);
// Aggregate orders for the dashboard: total items sold (sum of quantity), total revenue (sum of price), unique customers
$stats_res = mysqli_query($conn, "SELECT COALESCE(SUM(quantity),0) AS total_items_sold, COALESCE(SUM(price),0) AS total_revenue, COUNT(DISTINCT CONCAT(customer_name, contact)) AS total_customers_count FROM orders $date_query");
$stats_row = mysqli_fetch_assoc($stats_res);
$total_items_sold = intval($stats_row['total_items_sold'] ?? 0);
$total_revenue = floatval($stats_row['total_revenue'] ?? 0);
$total_customers_count = intval($stats_row['total_customers_count'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="image/photo_2025-08-21_13-04-14.jpg" type="image/x-icon">
    <title>Admin Panel | LT-STORE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary: #1e293b; --accent: #3b82f6; --bg: #f8fafc; --danger: #ef4444; --success: #10b981; --text-main: #334155; --white: #ffffff; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg); display: flex; color: var(--text-main); }
        
        /* Mobile Nav */
        .mobile-nav { display: none; background: var(--primary); color: white; padding: 12px 20px; position: fixed; top: 0; width: 100%; z-index: 1000; justify-content: space-between; align-items: center; box-sizing: border-box; }
        .mobile-logo-group { display: flex; align-items: center; gap: 10px; }
        .mobile-logo-img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }

        /* Sidebar Header & Logo */
        .sidebar { width: var(--sidebar-width); background: var(--primary); color: white; height: 100vh; position: fixed; z-index: 100; transition: 0.3s; }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-logo-img { width: 85px; height: 85px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.1); object-fit: cover; margin-bottom: 15px; }
        .sidebar h2 { margin: 0; font-size: 1.3rem; letter-spacing: 1px; color: #fff; }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 15px 30px; display: flex; align-items: center; gap: 15px; transition: 0.2s; }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.05); color: white; }
        
        /* Content Area */
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; box-sizing: border-box; transition: 0.3s; }
        
        .date-filter-box { background: white; padding: 20px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .date-filter-box form { display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap; }
        .date-filter-box label { display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 5px; }

        .stats-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; }
        .content-box { background: var(--white); padding: 35px; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 40px; }
        
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th { text-align: left; padding: 15px; background: #f1f5f9; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 18px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .prod-img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .badge { padding: 6px 12px; border-radius: 30px; font-size: 0.7rem; font-weight: 700; display: inline-block; }
        .status-PENDING { background: #fef3c7; color: #92400e; }
        .status-SHIPPED { background: #dbeafe; color: #1e40af; }
        .status-DELIVERED { background: #d1fae5; color: #065f46; }
        
        /* Stock badges */
        .stock-badge { padding: 6px 10px; border-radius: 20px; font-size: 0.75rem; font-weight:700; }
        .stock-badge.in { background: #d1fae5; color: #065f46; }
        .stock-badge.out { background: #fee2e2; color: #991b1b; }

        /* Admin stock editor */
        .admin-stock-input { width: 80px; padding: 8px; border-radius:6px; border:1px solid #e2e8f0; }
        .btn-stock-save { padding:6px 10px; border-radius:6px; background:var(--accent); color:white; border:none; cursor:pointer; }
        .btn-stock-save:hover { filter:brightness(0.95); }

        input, textarea, select { width: 100%; padding: 12px; margin: 8px 0 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }        .btn-update { background: #3cb371; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-filter { background: var(--primary); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }        .btn-action { padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; border: 1px solid #ddd; background: white; transition: 0.2s; }
        
        #editOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center; }
        #editOverlay.open { display:flex; }
        .edit-card { background:white; padding:30px; border-radius:15px; width:90%; max-width:500px; transform: scale(0.95); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease; }
        #editOverlay.open .edit-card { transform: scale(1); opacity:1; }

        /* View modal */
        #viewOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.45); z-index:2100; justify-content:center; align-items:center; padding:20px; box-sizing:border-box; }
        #viewOverlay.open { display:flex; }
        .view-card { background:white; padding:20px; border-radius:12px; width:90%; max-width:700px; transform: scale(0.95); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease; }
        #viewOverlay.open .view-card { transform: scale(1); opacity:1; } 

        /* Animation for row update */
        @keyframes highlight { 0% { background: #d1fae5; } 100% { background: transparent; } }
        .row-update-success { animation: highlight 1.5s ease forwards; }

        /* Toast */
        #toast { position: fixed; top: 20px; right: 20px; min-width: 220px; padding: 12px 18px; background: #10b981; color: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.15); transform: translateY(-20px); opacity: 0; transition: transform 0.25s ease, opacity 0.25s ease; z-index: 3000; }
        #toast.show { transform: translateY(0); opacity: 1; }
        #toast.success { background: #10b981; }
        #toast.error { background: #ef4444; }




        @media (max-width: 768px) {
            .mobile-nav { display: flex; }
            .sidebar { left: -100%; top: 60px; height: calc(100vh - 60px); width: 100%; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; width: 100%; padding: 80px 15px 20px 15px; }
        }
    </style>
</head>
<body>

    <div class="mobile-nav">
        <div class="mobile-logo-group">
            <img src="image/photo_2025-08-21_13-04-14.jpg" class="mobile-logo-img" alt="Logo">
            <strong>LT-STORE ADMIN</strong>
        </div>
        <i class="fas fa-bars fa-lg" onclick="toggleSidebar()" style="cursor:pointer;"></i>
    </div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="image/photo_2025-08-21_13-04-14.jpg" class="admin-logo-img" alt="Logo">
            <h2>LT-STORE</h2>
            <p style="margin: 5px 0 0; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Control Panel</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="#orders" onclick="toggleSidebar()"><i class="fas fa-file-invoice-dollar"></i> Sales Report</a></li>
            <li><a href="#products" onclick="toggleSidebar()"><i class="fas fa-boxes"></i> Inventory</a></li>
            <li><a href="shop.php"><i class="fas fa-external-link-alt"></i> View Store</a></li>
            <li><a href="logout.php" style="color: var(--danger);"><i class="fas fa-power-off"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="date-filter-box">
            <form method="GET" action="admin.php">
                <div>
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div>
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div>
                    <button type="submit" class="btn-filter">Filter Report</button>
                    <a href="admin.php" class="btn-action" style="background: #b60505ff; color:white; text-decoration: none;">Reset</a>
                </div>
            </form>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Products & Stock</h3>
                    <p style="margin:0; font-size:1rem;"><strong id="total_products"><?php echo $total_products; ?></strong> products<br>
                    <span id="total_stock_items" style="color:#64748b; font-size:0.9rem;"><?php echo $total_stock_items; ?> items in stock</span></p>
                </div>
                <i class="fas fa-tags fa-2x" style="opacity:0.2;"></i>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Orders Found</h3>
                    <p style="font-size: 0.9rem; margin: 5px 0;"><strong><?php echo $total_customers_count; ?></strong> Customers</p>
                    <p style="font-size: 0.9rem; margin: 0;"><strong><?php echo $total_items_sold; ?></strong> Items Sold</p>
                </div>
                <i class="fas fa-shopping-bag fa-2x" style="opacity:0.2;"></i>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Revenue</h3>
                    <p>$<?php echo number_format($total_revenue, 2); ?></p>
                </div>
                <i class="fas fa-coins fa-2x" style="opacity:0.2;"></i>
            </div>
        </div>

        <div class="content-box" id="orders">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;"><i class="fas fa-file-invoice-dollar" style="color:var(--accent);"></i> Order Management</h2>
                <div class="action-btns">
                    <button onclick="printSelectedReport()" class="btn-action" style="background-color: #333; color:white; border:none;"><i class="fas fa-print"></i> Print Report</button>
                    <button onclick="printMultiInvoice()" class="btn-action" style="background: #eb0f0fff; color:white; border:none;">Print Invoices</button>
                </div>
            </div>
            
            <input type="text" id="orderSearch" placeholder="Search orders..." onkeyup="filterOrders()" style="width: 100%; max-width: 300px; margin-top: 20px;">
            
            <div class="table-responsive">
                <table id="orderTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="toggleAll(this)"></th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Summary</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders = mysqli_query($conn, "SELECT MAX(id) AS id, customer_name, contact, MAX(status) AS status, MAX(description) AS first_desc, COALESCE(SUM(quantity),0) AS item_count FROM orders $date_query GROUP BY customer_name, contact ORDER BY id DESC");
                        while($o = mysqli_fetch_assoc($orders)) {
                            $current_status = strtoupper($o['status'] ?? 'PENDING');
                        ?>
                        <tr class="order-row">
                            <td><input type="checkbox" class="row-checkbox" data-id="<?php echo $o['id']; ?>"></td>
                            <td>
                                <div style="font-weight:700;"><?php echo $o['customer_name']; ?></div>
                                <div style="font-size:0.8rem; color:#94a3b8;"><?php echo $o['contact']; ?></div>
                            </td>
                            <td><?php echo $o['item_count']; ?></td>
                            <td style="font-size: 0.85rem; max-width: 200px;"><?php echo $o['first_desc']; ?></td>
                            <td><span class="badge status-<?php echo $current_status; ?>"><?php echo $current_status; ?></span></td>
                            <td>
                                <form method="POST" style="display:flex; gap:5px; margin:0;">
                                    <input type="hidden" name="customer_name" value="<?php echo $o['customer_name']; ?>">
                                    <input type="hidden" name="contact" value="<?php echo $o['contact']; ?>">
                                    <select name="status" style="padding:5px; margin:0; width:auto; font-size:0.75rem;">
                                        <option value="Pending" <?php if($current_status == 'PENDING') echo 'selected'; ?>>Pending</option>
                                        <option value="Shipped" <?php if($current_status == 'SHIPPED') echo 'selected'; ?>>Shipped</option>
                                        <option value="Delivered" <?php if($current_status == 'DELIVERED') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">OK</button>
                                </form>
                            </td>
                            <td>
                                <a href="#" onclick="viewOrders('<?php echo addslashes($o['customer_name']); ?>','<?php echo addslashes($o['contact']); ?>'); return false;" style="color:#0ea5e9; margin-right:10px;"><i class="fas fa-eye"></i></a>
                                <a href="invoice.php?id=<?php echo $o['id']; ?>" target="_blank" style="color:var(--accent); margin-right:10px;"><i class="fas fa-file-invoice"></i></a>
                                <a href="admin.php?delete_customer_orders=1&name=<?php echo urlencode($o['customer_name']); ?>&contact=<?php echo urlencode($o['contact']); ?>" onclick="return confirm('Delete all orders for this customer?')" style="color:var(--danger);"><i class="fas fa-trash-alt"></i></a>
                            </td> 
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-box" id="products">
            <h2><i class="fas fa-boxes" style="color:var(--accent);"></i> Inventory Manager</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Product Name" required>
                <input type="number" step="0.01" name="price" placeholder="Price ($)" required>
                <textarea name="description" rows="3" placeholder="Description"></textarea>
                <label style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Product Image</label>
                <input type="file" name="image" accept="image/*">
                <!-- Stock status is determined automatically from quantity -->
                <label style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-top:8px;">Stock Quantity</label>
                <input type="number" name="stock_qty" value="0" min="0" style="padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                <button type="submit" name="add_product" style="background:var(--primary); color:white; border:none; padding:15px; border-radius:8px; cursor:pointer; width:100%; font-weight:700;">Add Product</button>
            </form>
            
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Img</th><th>Product</th><th>Price</th><th>Stock</th><th style="text-align:right;">Manage</th></tr></thead>
                    <tbody>
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
                        while($p = mysqli_fetch_assoc($res)) {
                            $img = !empty($p['image']) ? $p['image'] : 'default.jpg';
                        ?>
                        <tr>
                            <td><img src="uploads/<?php echo $img; ?>" class="prod-img"></td>
                            <td>
                                <strong><?php echo $p['name']; ?></strong>
                                <div style="margin-top:6px;">
                                    <span class="badge stock-badge <?php echo ($p['stock_status']=='In Stock' ? 'in' : 'out'); ?>"><?php echo $p['stock_status']; ?></span>
                                </div>
                            </td>
                            <td style="color:var(--success); font-weight:700;">$<?php echo number_format($p['price'], 2); ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:8px; align-items:center; justify-content:center;">
                                    <input type="number" class="admin-stock-input" data-id="<?php echo $p['id']; ?>" value="<?php echo intval($p['stock_qty']); ?>" min="0" style="width:80px; padding:6px; border-radius:6px; border:1px solid #e2e8f0;">
                                    <button class="btn-stock-save" data-id="<?php echo $p['id']; ?>" style="padding:6px 10px; border-radius:6px; background:var(--accent); color:white; border:none; cursor:pointer;">Save</button>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <!-- Manual stock status removed; status is derived from stock quantity -->
                                <button onclick="openEdit(<?php echo htmlspecialchars(json_encode($p)); ?>)" style="background:none; border:none; color:var(--accent); cursor:pointer; margin-right:10px; font-weight: 600;">Edit</button>
                                <a href="admin.php?delete_id=<?php echo $p['id']; ?>" onclick="return confirm('Delete this product?')" style="color:var(--danger); text-decoration:none; font-weight: 600;">Delete</a>
                            </td>
                        </tr> 
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="editOverlay">
        <div class="edit-card">
            <h3>Update Product</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_id">
                <input type="text" name="name" id="edit_name" required>
                <input type="number" step="0.01" name="price" id="edit_price" required>
                <textarea name="description" id="edit_desc" rows="3"></textarea>
                <!-- Stock status is determined automatically from quantity -->
                <label style="font-size:0.85rem; font-weight:700; color:#64748b; margin-top:6px; display:block;">Stock Quantity</label>
                <input type="number" name="stock_qty" id="edit_qty" value="0" min="0" style="padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                <input type="file" name="image" accept="image/*">
                <div style="display:flex; gap:10px; margin-top:12px;">
                    <button type="submit" name="update_product" class="btn-update" style="flex:1;">Update</button>
                    <button type="button" onclick="closeEdit()" style="flex:1; background:#ddd; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="viewOverlay">
        <div class="view-card">
            <button onclick="closeView()" style="float:right; border:none; background:none; font-size:1.25rem; cursor:pointer;">&times;</button>
            <div id="viewContent" style="clear:both; margin-top:10px;">
                <!-- Order details get loaded here -->
            </div>
        </div>
    </div> 



    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('active'); }

        function filterOrders() {
            let input = document.getElementById('orderSearch').value.toUpperCase();
            let rows = document.querySelectorAll('#orderTable tbody tr');
            rows.forEach(row => { row.style.display = row.innerText.toUpperCase().includes(input) ? "" : "none"; });
        }
        
        function toggleAll(source) { document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = source.checked); }

        function printSelectedReport() {
            let checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) return alert("Select at least one order first.");
            let ids = Array.from(checked).map(cb => cb.getAttribute('data-id')).join(',');
            let start = document.getElementsByName('start_date')[0].value;
            let end = document.getElementsByName('end_date')[0].value;
            window.open(`report_print.php?ids=${ids}&start=${start}&end=${end}`, '_blank');
        }

        function printMultiInvoice() {
            let checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) return alert("Please select an order.");
            let ids = Array.from(checked).map(cb => cb.getAttribute('data-id')).join(',');
            window.open('invoice.php?ids=' + ids, '_blank');
        }

        function openEdit(p) {
            document.getElementById('edit_id').value = p.id;
            document.getElementById('edit_name').value = p.name;
            document.getElementById('edit_price').value = p.price;
            document.getElementById('edit_desc').value = p.description;
            if (document.getElementById('edit_qty')) {
                document.getElementById('edit_qty').value = p.stock_qty ?? 0;
            }

            const overlay = document.getElementById('editOverlay');
            overlay.classList.add('open');
        }

        // Overlay submit handler defined separately so it can be attached/removed safely

        function closeEdit() { 
            const overlay = document.getElementById('editOverlay');
            overlay.classList.remove('open');
        }

        // View customer orders in modal
        function viewOrders(name, contact) {
            const start = document.getElementsByName('start_date')[0].value;
            const end = document.getElementsByName('end_date')[0].value;
            const url = `admin.php?view_orders=1&name=${encodeURIComponent(name)}&contact=${encodeURIComponent(contact)}&start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`;
            const content = document.getElementById('viewContent');
            if (!content) return alert('View container missing');
            content.innerHTML = 'Loading...';
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    content.innerHTML = html;
                    document.getElementById('viewOverlay').classList.add('open');
                }).catch(err => {
                    showToast('Failed to load details', 'error');
                    console.error(err);
                });
        }
        function closeView() { document.getElementById('viewOverlay').classList.remove('open'); }

        // Print current modal content
        function printView() {
            const content = document.getElementById('viewContent');
            if (!content) return showToast('Nothing to print', 'error');
            const w = window.open('', '_blank');
            const style = `
                <style>
                    body{font-family: Inter, Arial, sans-serif; color:#111; padding:20px}
                    table{width:100%; border-collapse:collapse;}
                    th{background:#f7fafc; padding:8px; text-align:left; color:#334155}
                    td{padding:8px; border-bottom:1px solid #eef2f7}
                    img{max-width:60px; max-height:60px; object-fit:cover}
                </style>`;
            w.document.write('<html><head><title>Orders</title>'+style+'</head><body>' + content.innerHTML + '</body></html>');
            w.document.close();
            w.focus();
            w.print();
            // w.close(); // keep window open so user can view
        }

        // Toast helper
        function showToast(msg, type='success') {
            let toast = document.getElementById('toast');
            if(!toast) {
                toast = document.createElement('div');
                toast.id = 'toast';
                document.body.appendChild(toast);
            }
            toast.className = type === 'success' ? 'success' : 'error';
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }



        // Handle inline stock qty save via AJAX
        document.querySelectorAll('.btn-stock-save').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const input = document.querySelector('.admin-stock-input[data-id="' + id + '"]');
                if (!input) return;
                const qty = parseInt(input.value) || 0;
                const formData = new FormData();
                formData.append('update_stock', '1');
                formData.append('product_id', id);
                formData.append('stock_qty', qty);
                formData.append('ajax', '1');
                fetch('admin.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        const row = btn.closest('tr');
                        const badge = row.querySelector('.stock-badge');
                        badge.textContent = data.stock_status;
                        if (data.stock_status === 'In Stock') { badge.classList.remove('out'); badge.classList.add('in'); }
                        else { badge.classList.remove('in'); badge.classList.add('out'); }
                        // update quantity input to reflect saved value
                        const inputField = row.querySelector('.admin-stock-input');
                        if (inputField) inputField.value = data.stock_qty;
                        // update dashboard total items
                        const totalStockEl = document.getElementById('total_stock_items');
                        if (totalStockEl && typeof data.total_stock_items !== 'undefined') {
                            totalStockEl.textContent = data.total_stock_items + ' items in stock';
                        }
                        showToast('Quantity updated', 'success');
                    } else {
                        showToast('Update failed', 'error');
                    }
                }).catch(err => { showToast('Update failed', 'error'); console.error(err); });
            });
        });
        // Handle status update forms via AJAX
        document.querySelectorAll('form').forEach(form => {
            if (form.querySelector('button[name="update_status"]')) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = form.querySelector('button[name="update_status"]');
                    btn.disabled = true;
                    const formData = new FormData(form);
                    // Ensure the server detects this as an update_status submission when using AJAX
                    formData.append('update_status', '1');
                    formData.append('ajax', '1');
                    fetch('admin.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        if (data && data.success) {
                            const row = form.closest('tr');
                            const statusSpan = row.querySelector('.badge');
                            const statusText = (data.status || '').toUpperCase();
                            statusSpan.textContent = statusText;
                            statusSpan.className = 'badge status-' + statusText;
                            row.classList.add('row-update-success');
                            setTimeout(() => row.classList.remove('row-update-success'), 1800);
                            showToast('Status updated', 'success');
                        } else {
                            showToast('Update failed', 'error');
                        }
                    }).catch(err => {
                        btn.disabled = false;
                        showToast('Update failed', 'error');
                        console.error(err);
                    });
                });
            }
        });









        // Show toast on page load if ?msg=
        (function() {
            const params = new URLSearchParams(window.location.search);
            const m = params.get('msg');
            if (m) {
                const map = {
                    'status_updated': 'Status updated',
                    'updated': 'Product updated',
                    'added': 'Product added',
                    'deleted': 'Product deleted',
                    'order_deleted': 'Orders deleted'
                };
                const text = map[m] || 'Action completed';
                showToast(text, 'success');
                // remove msg param from URL without reloading
                params.delete('msg');
                window.history.replaceState({}, '', location.pathname + (params.toString() ? ('?' + params.toString()) : ''));
            }
        })();

        // Poll admin for product stock updates and dashboard totals every 8s
        (function pollAdminProducts(){
            fetch('admin.php?poll_products=1')
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        const totalEl = document.getElementById('total_stock_items');
                        if (totalEl && typeof data.total_stock_items !== 'undefined') {
                            totalEl.textContent = data.total_stock_items + ' items in stock';
                        }
                        const totalCountEl = document.getElementById('total_products');
                        if (totalCountEl && typeof data.total_products !== 'undefined') {
                            totalCountEl.textContent = data.total_products;
                        }
                        (data.products || []).forEach(p => {
                            const input = document.querySelector('.admin-stock-input[data-id="' + p.id + '"]');
                            if (input) {
                                if (parseInt(input.value || '0') !== p.stock_qty) input.value = p.stock_qty;
                                const row = input.closest('tr');
                                const badge = row ? row.querySelector('.stock-badge') : null;
                                if (badge) {
                                    badge.textContent = p.stock_status;
                                    if (p.stock_status === 'In Stock') { badge.classList.remove('out'); badge.classList.add('in'); } else { badge.classList.remove('in'); badge.classList.add('out'); }
                                }
                            }
                        });
                    }
                }).catch(err => { /* silent */ console.error(err); })
                .finally(() => setTimeout(pollAdminProducts, 8000));
        })();
        </script>
</body>
</html>