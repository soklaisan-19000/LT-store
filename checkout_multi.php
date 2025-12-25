<?php
session_start();
include 'db.php';

// 1. Check if the order was just completed
$order_placed = isset($_GET['success']) ? true : false;
$customer_display_name = isset($_GET['user']) ? htmlspecialchars($_GET['user']) : 'Customer';

// 2. Redirect if cart is empty and no success message is present
if (!$order_placed && empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit();
}

$grand_total = 0;
$items_to_buy = [];

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id) {
        $safe_id = mysqli_real_escape_string($conn, $product_id);
        $res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$safe_id'");
        $p = mysqli_fetch_assoc($res);
        if ($p) {
            $items_to_buy[] = $p;
            $grand_total += $p['price'];
        }
    }
}

// 3. Handle Order Submission
if (isset($_POST['place_order'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $customer_note = mysqli_real_escape_string($conn, $_POST['customer_note']); 
    $order_date = date('Y-m-d H:i:s');

    foreach ($items_to_buy as $item) {
        $p_name = mysqli_real_escape_string($conn, $item['name']);
        $p_price = $item['price'];
        $p_img = $item['image'];

        $sql = "INSERT INTO orders (product_name, description, price, customer_name, contact, product_image, order_date, status) 
                VALUES ('$p_name', '$customer_note', '$p_price', '$customer_name', '$contact', '$p_img', '$order_date', 'Pending')";
        
        mysqli_query($conn, $sql);
    }

    $_SESSION['cart'] = [];
    // UPDATED FILENAME HERE
    header("Location: checkout_multi.php?success=1&user=" . urlencode($customer_name));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | LT SMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; padding: 40px; color: #334155; margin: 0; }
        .container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 380px; gap: 30px; }
        .card { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #64748b; font-size: 0.85rem; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; box-sizing: border-box; }
        .btn-order { background: #1e293b; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; }

        /* Success Overlay */
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .success-box { background: white; padding: 40px; border-radius: 20px; text-align: center; max-width: 400px; animation: pop 0.4s ease; }
        .success-box i { font-size: 50px; color: #10b981; margin-bottom: 15px; }
        @keyframes pop { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

<?php if ($order_placed): ?>
    <div class="overlay">
        <div class="success-box">
            <i class="fas fa-check-circle"></i>
            <h2>Thank You!</h2>
            <p>Hi <strong><?php echo $customer_display_name; ?></strong>, your order has been placed successfully.</p>
            <a href="shop.php" style="background: #10b981; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; display: inline-block;">Back to Shop</a>
        </div>
    </div>
<?php endif; ?>

<div class="container">
    <div class="card">
        <h2>Customer Information</h2>
        <form method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="input-group">
                <label>Contact</label>
                <input type="text" name="contact" required>
            </div>
            <div class="input-group">
                <label>Shipping Address</label>
                <textarea name="address" rows="2" required></textarea>
            </div>
            <div class="input-group">
                <label>Notes</label>
                <textarea name="customer_note" rows="3"></textarea>
            </div>
            <button type="submit" name="place_order" class="btn-order">Place Order</button>
        </form>
    </div>

    <div class="card">
        <h3>Summary</h3>
        <?php foreach($items_to_buy as $item): ?>
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <img src="uploads/<?php echo $item['image']; ?>" width="40" height="40" style="border-radius:5px;">
                <div>
                    <div><?php echo $item['name']; ?></div>
                    <div style="color:#10b981;">$<?php echo number_format($item['price'], 2); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        <hr>
        <div style="display:flex; justify-content:space-between; font-weight:bold;">
            <span>Total:</span>
            <span>$<?php echo number_format($grand_total, 2); ?></span>
        </div>
    </div>
</div>

</body>
</html>