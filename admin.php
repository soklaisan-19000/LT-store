<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 1. UNIQUE FEATURE: Handle Status Updates
if (isset($_POST['update_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status='$new_status' WHERE id='$order_id'");
    header("Location: admin.php?msg=status_updated");
    exit();
}

// 2. Handle Deleting a Product
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $res = mysqli_query($conn, "SELECT image FROM products WHERE id='$id'");
    $p = mysqli_fetch_assoc($res);
    if($p['image'] != 'default.jpg') { @unlink("uploads/".$p['image']); }
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    header("Location: admin.php?msg=deleted");
    exit();
}

// 3. Handle Adding a Product
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    
    if(!empty($imageName)) {
        $uniqueName = time() . "_" . $imageName;
        move_uploaded_file($imageTmp, "uploads/" . $uniqueName);
    } else { $uniqueName = "default.jpg"; }

    mysqli_query($conn, "INSERT INTO products (name, price, description, image) VALUES ('$name', '$price', '$desc', '$uniqueName')");
    header("Location: admin.php?msg=added");
    exit();
}

// Stats Calculations
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$res_orders = mysqli_query($conn, "SELECT id, price FROM orders");
$total_orders = mysqli_num_rows($res_orders);
$total_revenue = 0;
while($row = mysqli_fetch_assoc($res_orders)) { $total_revenue += $row['price']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | SMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --sidebar-width: 240px; 
            --primary: #2c3e50; 
            --accent: #3498db; 
            --bg: #f4f7f6; 
            --danger: #e74c3c; 
            --success: #27ae60; 
            --text-main: #34495e;
        }
        
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: var(--bg); display: flex; color: var(--text-main); scroll-behavior: smooth; }
        
        .sidebar { width: var(--sidebar-width); background: var(--primary); color: white; height: 100vh; position: fixed; box-shadow: 2px 0 5px rgba(0,0,0,0.1); z-index: 100; }
        .sidebar h2 { text-align: center; padding: 25px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin: 0; letter-spacing: 1px; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 10px; }
        .sidebar-menu li { border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-menu a { color: #bdc3c7; text-decoration: none; padding: 15px 25px; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.1); color: white; }
        
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; }
        
        .stats-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid var(--accent); }
        .stat-card.revenue { border-left-color: var(--success); }
        .stat-card i { font-size: 2.5rem; opacity: 0.2; }
        .stat-info h3 { margin: 0; font-size: 0.9rem; text-transform: uppercase; color: #7f8c8d; }
        .stat-info p { margin: 5px 0 0; font-size: 1.8rem; font-weight: bold; color: var(--primary); }
        
        .content-box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .content-box h2 { margin-top: 0; display: flex; align-items: center; gap: 10px; font-size: 1.4rem; border-bottom: 2px solid #f1f1f1; padding-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; background: #f9fafb; color: #7f8c8d; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; }
        td { padding: 15px; border-bottom: 1px solid #f1f1f1; vertical-align: middle; }
        tr:hover td { background: #fcfcfc; }
        
        .prod-img { width: 55px; height: 55px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Shipped { background: #e0f2fe; color: #0369a1; }
        .status-Delivered { background: #e6f4ea; color: var(--success); }

        .search-wrapper { position: relative; margin-bottom: 15px; }
        .search-bar { width: 250px; padding: 8px 15px 8px 35px; border: 1px solid #ddd; border-radius: 6px; outline: none; transition: 0.3s; }
        .search-bar:focus { border-color: var(--accent); width: 300px; }
        .search-icon { position: absolute; left: 12px; top: 10px; color: #95a5a6; }

        input[type="text"], input[type="number"], textarea {
            width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;
        }

        .btn-update { background: var(--accent); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }

        @media print {
            .sidebar, .stats-container, #products, form, .btn-print, .btn-del, .update-col, .search-wrapper { display: none !important; }
            .main-content { margin: 0; width: 100%; padding: 0; }
            .content-box { box-shadow: none; border: 1px solid #eee; padding: 10px; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>LT ADMIN</h2>
        <ul class="sidebar-menu">
            <li><a href="#orders"><i class="fas fa-chart-line"></i> Sales Report</a></li>
            <li><a href="#products"><i class="fas fa-boxes"></i> Inventory</a></li>
            <li><a href="shop.php"><i class="fas fa-external-link-alt"></i> Live Store</a></li>
            <li><a href="logout.php" style="color: #e74c3c;"><i class="fas fa-power-off"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info"><h3>Total Items</h3><p><?php echo $total_products; ?></p></div>
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-card">
                <div class="stat-info"><h3>Total Orders</h3><p><?php echo $total_orders; ?></p></div>
                <i class="fas fa-shopping-basket"></i>
            </div>
            <div class="stat-card revenue">
                <div class="stat-info"><h3>Gross Revenue</h3><p>$<?php echo number_format($total_revenue, 2); ?></p></div>
                <i class="fas fa-wallet"></i>
            </div>
        </div>

        <div class="content-box" id="orders">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2><i class="fas fa-file-invoice-dollar" style="color:var(--accent);"></i> Sales Report Summary</h2>
                <button onclick="window.print()" class="btn-print"><i class="fas fa-download"></i> Export PDF / Print</button>
            </div>
            
            <div style="display:flex; justify-content: space-between; align-items: center; margin-top:10px;">
                <p style="color: #7f8c8d; font-size: 0.9rem;">Report Generated: <?php echo date('d M Y'); ?></p>
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="orderSearch" class="search-bar" placeholder="Search customer or product..." onkeyup="filterOrders()">
                </div>
            </div>

            <table id="orderTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Product Details</th>
                        <th>Description</th> <th>Customer Information</th>
                        <th>Status</th>
                        <th class="update-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
                    while($o = mysqli_fetch_assoc($orders)) {
                        $current_status = $o['status'] ?? 'Pending';
                        $o_img = !empty($o['product_image']) ? $o['product_image'] : 'default.jpg';
                        echo "<tr>
                                <td><img src='uploads/$o_img' class='prod-img'></td>
                                <td>
                                    <div style='font-weight:bold; color:var(--primary);'>{$o['product_name']}</div>
                                    <div style='color:var(--success); font-weight:bold;'>$".number_format($o['price'],2)."</div>
                                </td>
                                <td style='font-size:0.85rem; color:#7f8c8d; max-width:200px; word-wrap:break-word;'>
                                    " . (!empty($o['description']) ? $o['description'] : 'N/A') . "
                                </td>
                                <td>
                                    <div style='font-weight:600;'>{$o['customer_name']}</div>
                                    <div style='font-size:0.8rem; color:#95a5a6;'>{$o['contact']}</div>
                                </td>
                                <td><span class='badge status-$current_status'>$current_status</span></td>
                                <td class='update-col'>
                                    <form method='POST' style='display:flex; gap:5px;'>
                                        <input type='hidden' name='order_id' value='{$o['id']}'>
                                        <select name='status' style='padding:5px; border-radius:4px; margin:0; width:auto; font-size:0.8rem;'>
                                            <option value='Pending' ".($current_status == 'Pending' ? 'selected' : '').">Pending</option>
                                            <option value='Shipped' ".($current_status == 'Shipped' ? 'selected' : '').">Shipped</option>
                                            <option value='Delivered' ".($current_status == 'Delivered' ? 'selected' : '').">Delivered</option>
                                        </select>
                                        <button type='submit' name='update_status' class='btn-update'>OK</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="content-box" id="products">
            <h2><i class="fas fa-warehouse" style="color:var(--accent);"></i> Inventory Management</h2>
            <form method="POST" enctype="multipart/form-data" style="background: #fcfcfc; padding: 20px; border-radius: 8px; border: 1px dashed #ddd;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:bold; color:#7f8c8d;">Product Title</label>
                        <input type="text" name="name" placeholder="e.g. Mechanical Keyboard" required>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:bold; color:#7f8c8d;">Unit Price ($)</label>
                        <input type="number" step="0.01" name="price" placeholder="0.00" required>
                    </div>
                </div>
                <label style="font-size:0.8rem; font-weight:bold; color:#7f8c8d;">Product Description</label>
                <textarea name="description" placeholder="Brief details about the product..."></textarea>
                
                <label style="font-size:0.8rem; font-weight:bold; color:#7f8c8d;">Display Image</label>
                <input type="file" name="image" accept="image/*" style="border:none; padding: 10px 0;">
                
                <button type="submit" name="add_product" style="background:var(--primary); color:white; border:none; padding:12px; border-radius:6px; cursor:pointer; width:100%; font-weight:bold;"><i class="fas fa-plus-circle"></i> Update Catalog</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Preview</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM products");
                    while($p = mysqli_fetch_assoc($res)) {
                        $img = !empty($p['image']) ? $p['image'] : 'default.jpg';
                        echo "<tr>
                                <td><img src='uploads/$img' class='prod-img'></td>
                                <td style='font-weight:600;'>{$p['name']}</td>
                                <td style='color:var(--success); font-weight:bold;'>\${$p['price']}</td>
                                <td style='text-align:right;'>
                                    <a href='admin.php?delete_id={$p['id']}' class='btn-del' onclick='return confirm(\"Remove product?\")' style='color:var(--danger); text-decoration:none;'>
                                        <i class='fas fa-times-circle'></i> Remove
                                    </a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterOrders() {
            let input = document.getElementById('orderSearch').value.toUpperCase();
            let rows = document.getElementById('orderTable').getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                let txt = rows[i].innerText.toUpperCase();
                rows[i].style.display = txt.indexOf(input) > -1 ? "" : "none";
            }
        }
    </script>
</body>
</html>