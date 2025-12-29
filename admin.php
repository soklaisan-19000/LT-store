<?php
session_start();
include 'db.php';

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
    mysqli_query($conn, "UPDATE orders SET status='$new_status' WHERE customer_name='$c_name' AND contact='$c_contact'");
    header("Location: admin.php?msg=status_updated");
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

if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $imageName = $_FILES['image']['name'];
    if(!empty($imageName)) {
        $uniqueName = time() . "_" . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $uniqueName);
    } else { $uniqueName = "default.jpg"; }
    mysqli_query($conn, "INSERT INTO products (name, price, description, image) VALUES ('$name', '$price', '$desc', '$uniqueName')");
    header("Location: admin.php?msg=added");
    exit();
}

if (isset($_POST['update_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    if(!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
        $img_query = ", image='$imageName'";
    } else { $img_query = ""; }
    mysqli_query($conn, "UPDATE products SET name='$name', price='$price', description='$desc' $img_query WHERE id='$id'");
    header("Location: admin.php?msg=updated");
    exit();
}

// DASHBOARD STATS
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$res_orders = mysqli_query($conn, "SELECT price, customer_name, contact FROM orders $date_query");
$total_items_sold = mysqli_num_rows($res_orders);

$total_revenue = 0;
$unique_customers = [];
while($row = mysqli_fetch_assoc($res_orders)) { 
    $total_revenue += $row['price']; 
    $customer_key = $row['customer_name'] . $row['contact'];
    $unique_customers[$customer_key] = true;
}
$total_customers_count = count($unique_customers);
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
        
        input, textarea, select { width: 100%; padding: 12px; margin: 8px 0 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        .btn-update { background: #3cb371; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-filter { background: var(--primary); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-action { padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; border: 1px solid #ddd; background: white; transition: 0.2s; }
        
        #editOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center; }
        .edit-card { background:white; padding:30px; border-radius:15px; width:90%; max-width:500px; }

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
                    <h3>Total Products</h3>
                    <p><?php echo $total_products; ?></p>
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
                        $orders = mysqli_query($conn, "SELECT id, customer_name, contact, status, MAX(description) as first_desc, COUNT(id) as item_count FROM orders $date_query GROUP BY customer_name, contact ORDER BY id DESC");
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
                <button type="submit" name="add_product" style="background:var(--primary); color:white; border:none; padding:15px; border-radius:8px; cursor:pointer; width:100%; font-weight:700;">Add Product</button>
            </form>
            
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Img</th><th>Product</th><th>Price</th><th style="text-align:right;">Manage</th></tr></thead>
                    <tbody>
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
                        while($p = mysqli_fetch_assoc($res)) {
                            $img = !empty($p['image']) ? $p['image'] : 'default.jpg';
                        ?>
                        <tr>
                            <td><img src="uploads/<?php echo $img; ?>" class="prod-img"></td>
                            <td><strong><?php echo $p['name']; ?></strong></td>
                            <td style="color:var(--success); font-weight:700;">$<?php echo number_format($p['price'], 2); ?></td>
                            <td style="text-align:right;">
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
                <input type="file" name="image" accept="image/*">
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="update_product" class="btn-update" style="flex:1;">Update</button>
                    <button type="button" onclick="closeEdit()" style="flex:1; background:#ddd; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
                </div>
            </form>
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
            document.getElementById('editOverlay').style.display = 'flex';
        }
        function closeEdit() { document.getElementById('editOverlay').style.display = 'none'; }
    </script>
</body>
</html>