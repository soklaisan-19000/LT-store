<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");

if(isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
    $item = mysqli_fetch_assoc($res);
} else {
    header("Location: shop.php");
    exit();
}

if (isset($_POST['place_order'])) {
    $p_name = $item['name'];
    $p_price = $item['price'];
    $p_image = $item['image']; // Capture the image name
    $cust_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $cust_contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $cust_address = mysqli_real_escape_string($conn, $_POST['address']);
    $cust_note = mysqli_real_escape_string($conn, $_POST['customer_note']); 
    
    // Updated SQL to include product_image
    $sql = "INSERT INTO orders (product_name, price, customer_name, contact, address, customer_note, product_image) 
            VALUES ('$p_name', '$p_price', '$cust_name', '$cust_contact', '$cust_address', '$cust_note', '$p_image')";
    
    if(mysqli_query($conn, $sql)) {
        $success = "Thank you, $cust_name! Your order for " . $item['name'] . " has been placed.";
    } else {
        $error = "Something went wrong: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 40px 20px; display: flex; justify-content: center; }
        .checkout-container { width: 100%; max-width: 500px; }
        .checkout-box { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .product-summary { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 15px; border: 1px solid #eee; }
        .product-summary img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
        .product-info h3 { margin: 0; font-size: 1.1rem; color: #2c3e50; }
        .product-desc { margin: 5px 0; font-size: 0.85rem; color: #7f8c8d; line-height: 1.4; }
        .product-info .price { margin: 8px 0 0; color: #28a745; font-weight: bold; font-size: 1.2rem; }
        h2 { margin: 0 0 20px; color: #2c3e50; text-align: center; }
        .form-group { margin-bottom: 15px; position: relative; }
        .form-group i { position: absolute; left: 15px; top: 18px; color: #95a5a6; }
        input, textarea { width: 100%; padding: 15px 15px 15px 45px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1rem; transition: 0.3s; font-family: inherit; }
        textarea { padding-top: 15px; height: 80px; resize: none; }
        .confirm-btn { width: 100%; padding: 15px; background: #3498db; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1.1rem; font-weight: bold; margin-top: 10px; }
        .success-card { text-align: center; }
        .success-card i { font-size: 4rem; color: #2ecc71; margin-bottom: 20px; }
        .cancel-link { display: block; text-align: center; margin-top: 20px; color: #95a5a6; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-box">
            <?php if(isset($success)): ?>
                <div class="success-card">
                    <i class="fas fa-check-circle"></i>
                    <h2 style="color: #2ecc71;">Order Placed!</h2>
                    <p><?php echo $success; ?></p>
                    <a href="shop.php" class="confirm-btn" style="text-decoration: none; display: block;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <h2><i class="fas fa-lock"></i> Checkout</h2>
                <div class="product-summary">
                    <?php $img = !empty($item['image']) ? $item['image'] : 'default.jpg'; ?>
                    <img src="uploads/<?php echo $img; ?>" alt="Product">
                    <div class="product-info">
                        <h3><?php echo $item['name']; ?></h3>
                        <p class="product-desc"><?php echo $item['description']; ?></p>
                        <p class="price">$<?php echo $item['price']; ?></p>
                    </div>
                </div>
                <form method="POST">
                    <div class="form-group"><i class="fas fa-user"></i><input type="text" name="full_name" placeholder="Full Name" required></div>
                    <div class="form-group"><i class="fas fa-phone"></i><input type="text" name="contact" placeholder="Phone Number" required></div>
                    <div class="form-group"><i class="fas fa-map-marker-alt"></i><input type="text" name="address" placeholder="Delivery Address" required></div>
                    <div class="form-group"><i class="fas fa-comment-alt"></i><textarea name="customer_note" placeholder="Order Notes (Optional)..."></textarea></div>
                    <button type="submit" name="place_order" class="confirm-btn">Confirm Purchase</button>
                </form>
                <a href="shop.php" class="cancel-link">Return to Store</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>