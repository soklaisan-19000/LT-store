<?php
session_start();
include 'db.php';

// Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Handle Status Updates
if (isset($_POST['update_status'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $c_contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    mysqli_query($conn, "UPDATE orders SET status='$new_status' WHERE customer_name='$c_name' AND contact='$c_contact'");
    header("Location: admin.php?msg=status_updated");
    exit();
}

// 2. Handle Deleting an Order Record
if (isset($_GET['delete_customer_orders'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $contact = mysqli_real_escape_string($conn, $_GET['contact']);
    mysqli_query($conn, "DELETE FROM orders WHERE customer_name='$name' AND contact='$contact'");
    header("Location: admin.php?msg=order_deleted");
    exit();
}

// 3. Handle Deleting a Product
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $res = mysqli_query($conn, "SELECT image FROM products WHERE id='$id'");
    $p = mysqli_fetch_assoc($res);
    if($p && $p['image'] != 'default.jpg') { 
        @unlink("uploads/".$p['image']); 
    }
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    header("Location: admin.php?msg=deleted");
    exit();
}

// 4. Handle Adding a Product
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    
    if(!empty($imageName)) {
        $uniqueName = time() . "_" . $imageName;
        move_uploaded_file($imageTmp, "uploads/" . $uniqueName);
    } else { 
        $uniqueName = "default.jpg"; 
    }

    $query = "INSERT INTO products (name, price, description, image) VALUES ('$name', '$price', '$desc', '$uniqueName')";
    mysqli_query($conn, $query);
    header("Location: admin.php?msg=added");
    exit();
}

// 5. Handle Updating a Product
if (isset($_POST['update_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    if(!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
        $img_query = ", image='$imageName'";
    } else {
        $img_query = "";
    }

    $query = "UPDATE products SET name='$name', price='$price', description='$desc' $img_query WHERE id='$id'";
    mysqli_query($conn, $query);
    header("Location: admin.php?msg=updated");
    exit();
}

// Stats
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$res_orders = mysqli_query($conn, "SELECT price FROM orders");
$total_orders_count = mysqli_num_rows($res_orders);
$total_revenue = 0;
while($row = mysqli_fetch_assoc($res_orders)) { $total_revenue += $row['price']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | LT SMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary: #1e293b; --accent: #3b82f6; --bg: #f8fafc; --danger: #ef4444; --success: #10b981; --text-main: #334155; --white: #ffffff; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg); display: flex; color: var(--text-main); }
        .sidebar { width: var(--sidebar-width); background: var(--primary); color: white; height: 100vh; position: fixed; z-index: 100; }
        .sidebar h2 { text-align: center; padding: 30px 0; border-bottom: 1px solid rgba(255,255,255,0.05); margin: 0; font-size: 1.5rem; letter-spacing: 2px; }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 15px 30px; display: flex; align-items: center; gap: 15px; }
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; box-sizing: border-box; }
        .stats-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; }
        .content-box { background: var(--white); padding: 35px; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #f1f5f9; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 18px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .prod-img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .badge { padding: 6px 12px; border-radius: 30px; font-size: 0.7rem; font-weight: 700; display: inline-block; }
        .status-PENDING { background: #fef3c7; color: #92400e; }
        .status-SHIPPED { background: #dbeafe; color: #1e40af; }
        .status-DELIVERED { background: #d1fae5; color: #065f46; }
        input, textarea, select { width: 100%; padding: 12px; margin: 8px 0 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        .btn-update { background: var(--accent); color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        
        /* Button Styling */
        .action-btns { display: flex; gap: 10px; align-items: center; }
        .btn-action { padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; border: 1px solid #ddd; background: white; transition: 0.2s; }
        .btn-action:hover { background: #f1f5f9; }
        .btn-invoice { background: var(--accent); color: white; border-color: var(--accent); }
        .btn-invoice:hover { background: #2563eb; }

        #editOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; }
        .edit-card { background:white; padding:30px; border-radius:15px; width:500px; }

        @media print { 
            .sidebar, .stats-container, #products, form, .update-col, #orderSearch, .manage-col, .check-col, .action-btns { display: none !important; } 
            .main-content { margin: 0; padding: 0; width: 100%; } 
            .content-box { box-shadow: none; border: none; padding: 0; }
            tr:not(.selected-to-print) { display: none !important; } 
            tr.selected-to-print { display: table-row !important; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>SMS ADMIN</h2>
        <ul class="sidebar-menu">
            <li><a href="#orders"><i class="fas fa-chart-line"></i> Sales Report</a></li>
            <li><a href="#products"><i class="fas fa-boxes"></i> Inventory</a></li>
            <li><a href="shop.php"><i class="fas fa-external-link-alt"></i> View Store</a></li>
            <li><a href="logout.php" style="color: var(--danger);"><i class="fas fa-power-off"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="stats-container">
            <div class="stat-card"><div class="stat-info"><h3>Inventory</h3><p><?php echo $total_products; ?></p></div><i class="fas fa-tags"></i></div>
            <div class="stat-card"><div class="stat-info"><h3>Total Items Sold</h3><p><?php echo $total_orders_count; ?></p></div><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-card"><div class="stat-info"><h3>Gross Revenue</h3><p>$<?php echo number_format($total_revenue, 2); ?></p></div><i class="fas fa-coins"></i></div>
        </div>

        <div class="content-box" id="orders">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2><i class="fas fa-file-invoice-dollar" style="color:var(--accent);"></i> Customer Orders</h2>
                <div class="action-btns">
                    <button onclick="printSelectedReport()" class="btn-action"><i class="fas fa-file-alt"></i> Select Print Report</button>
                    <button onclick="printMultiInvoice()" class="btn-action btn-invoice"><i class="fas fa-print"></i> Print Multi Invoice</button>
                </div>
            </div>
            <input type="text" id="orderSearch" placeholder="Search customer..." onkeyup="filterOrders()" style="width: 250px; margin-top: 20px;">
            
            <table id="orderTable">
                <thead>
                    <tr>
                        <th class="check-col"><input type="checkbox" onclick="toggleAll(this)"></th>
                        <th>Customer</th>
                        <th>Total Items</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="update-col">Action</th>
                        <th class="manage-col">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = mysqli_query($conn, "SELECT id, customer_name, contact, status, MAX(description) as first_desc, COUNT(id) as item_count FROM orders GROUP BY customer_name, contact ORDER BY id DESC");
                    while($o = mysqli_fetch_assoc($orders)) {
                        $current_status = strtoupper($o['status'] ?? 'PENDING');
                        ?>
                        <tr class="order-row">
                            <td class="check-col">
                                <input type="checkbox" class="row-checkbox" 
                                       data-name="<?php echo htmlspecialchars($o['customer_name']); ?>" 
                                       data-contact="<?php echo htmlspecialchars($o['contact']); ?>"
                                       data-id="<?php echo $o['id']; ?>">
                            </td>
                            <td>
                                <div style="font-weight:700; color:var(--primary);"><?php echo $o['customer_name']; ?></div>
                                <div style="font-size:0.8rem; color:#94a3b8;"><?php echo $o['contact']; ?></div>
                            </td>
                            <td><span style="font-weight:600;"><?php echo $o['item_count']; ?> Products</span></td>
                            <td style="font-size: 0.85rem; color: #64748b; max-width: 250px;">
                                <i><?php echo !empty($o['first_desc']) ? nl2br($o['first_desc']) : 'No description'; ?></i>
                            </td>
                            <td><span class="badge status-<?php echo $current_status; ?>"><?php echo $current_status; ?></span></td>
                            <td class="update-col">
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="customer_name" value="<?php echo $o['customer_name']; ?>">
                                    <input type="hidden" name="contact" value="<?php echo $o['contact']; ?>">
                                    <select name="status" style="padding:6px; margin:0; width:auto; font-size:0.75rem;">
                                        <option value="Pending" <?php if($current_status == 'PENDING') echo 'selected'; ?>>Pending</option>
                                        <option value="Shipped" <?php if($current_status == 'SHIPPED') echo 'selected'; ?>>Shipped</option>
                                        <option value="Delivered" <?php if($current_status == 'DELIVERED') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">OK</button>
                                </form>
                            </td>
                            <td class="manage-col">
                                <a href="invoice.php?id=<?php echo $o['id']; ?>" target="_blank" style="color:var(--accent); margin-right:15px;"><i class="fas fa-file-invoice fa-lg"></i></a>
                                <a href="admin.php?delete_customer_orders=1&name=<?php echo urlencode($o['customer_name']); ?>&contact=<?php echo urlencode($o['contact']); ?>" onclick="return confirm('Delete?')" style="color:var(--danger);"><i class="fas fa-trash-alt fa-lg"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="content-box" id="products">
            <h2><i class="fas fa-boxes" style="color:var(--accent);"></i> Inventory Manager</h2>
            <form method="POST" enctype="multipart/form-data">
                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
                    <div><label>Product Name</label><input type="text" name="name" required></div>
                    <div><label>Price ($)</label><input type="number" step="0.01" name="price" required></div>
                </div>
                <label>Description</label><textarea name="description" rows="3"></textarea>
                <label>Image</label><input type="file" name="image" accept="image/*">
                <button type="submit" name="add_product" style="background:var(--primary); color:white; border:none; padding:15px; border-radius:8px; cursor:pointer; width:100%; font-weight:700;">Add Product</button>
            </form>
            <table style="margin-top: 30px;">
                <thead><tr><th style="width: 80px;">Img</th><th>Product</th><th>Price</th><th style="text-align:right;">Manage</th></tr></thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
                    while($p = mysqli_fetch_assoc($res)) {
                        $img = !empty($p['image']) ? $p['image'] : 'default.jpg';
                    ?>
                    <tr>
                        <td><img src="uploads/<?php echo $img; ?>" class="prod-img"></td>
                        <td><div style="font-weight:700;"><?php echo $p['name']; ?></div></td>
                        <td style="color:var(--success); font-weight:700;">$<?php echo $p['price']; ?></td>
                        <td style="text-align:right;">
                            <button onclick="openEdit(<?php echo htmlspecialchars(json_encode($p)); ?>)" style="background:none; border:none; color:var(--accent); cursor:pointer; margin-right:15px;">Edit</button>
                            <a href="admin.php?delete_id=<?php echo $p['id']; ?>" onclick="return confirm('Delete?')" style="color:var(--danger); text-decoration:none;">Delete</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="editOverlay"><div class="edit-card"><h3>Update Product</h3><form method="POST" enctype="multipart/form-data"><input type="hidden" name="product_id" id="edit_id"><input type="text" name="name" id="edit_name" required><input type="number" step="0.01" name="price" id="edit_price" required><textarea name="description" id="edit_desc" rows="3"></textarea><input type="file" name="image" accept="image/*"><div style="display:flex; gap:10px;"><button type="submit" name="update_product" class="btn-update" style="flex:1;">Update</button><button type="button" onclick="closeEdit()" style="flex:1;">Cancel</button></div></form></div></div>

    <script>
        function filterOrders() {
            let input = document.getElementById('orderSearch').value.toUpperCase();
            let rows = document.querySelectorAll('#orderTable tbody tr');
            rows.forEach(row => { row.style.display = row.innerText.toUpperCase().includes(input) ? "" : "none"; });
        }
        
        function toggleAll(source) { 
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = source.checked); 
        }

        // Function 1: Print the current table view (Report)
        function printSelectedReport() {
            let rows = document.querySelectorAll('.order-row');
            let sel = false;
            rows.forEach(r => { 
                if(r.querySelector('.row-checkbox').checked) { 
                    r.classList.add('selected-to-print'); 
                    sel = true; 
                } else {
                    r.classList.remove('selected-to-print');
                }
            });
            if(!sel) return alert("Select at least one order first.");
            window.print();
        }

        // Function 2: Open multiple invoices in a new tab
        function printMultiInvoice() {
            let checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) return alert("Please select an order.");

            // Create a comma separated list of IDs to send to invoice.php
            let ids = Array.from(checked).map(cb => cb.getAttribute('data-id')).join(',');
            
            // Redirect to a specialized print view (ensure your invoice.php can handle multiple IDs)
            window.open('invoice.php?ids=' + ids, '_blank');
        }

        function openEdit(p) { document.getElementById('edit_id').value = p.id; document.getElementById('edit_name').value = p.name; document.getElementById('edit_price').value = p.price; document.getElementById('edit_desc').value = p.description; document.getElementById('editOverlay').style.display = 'flex'; }
        function closeEdit() { document.getElementById('editOverlay').style.display = 'none'; }
    </script>
</body>
</html>