<?php
session_start();
include 'db.php'; // Ensure this file has your mysqli_connect details

// 1. Fetch Product Details for the selected ID
if(isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
    $item = mysqli_fetch_assoc($res);
    
    if (!$item) {
        header("Location: shop.php");
        exit();
    }
} else {
    // If we are not in success mode and no ID is provided, redirect
    if(!isset($_GET['success'])) {
        header("Location: shop.php");
        exit();
    }
}

// 2. Handle Order Submission
if (isset($_POST['place_order'])) {
    $p_name = mysqli_real_escape_string($conn, $item['name']);
    $p_price = $item['price'];
    $p_image = $item['image']; 
    
    $cust_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $cust_contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $cust_address = mysqli_real_escape_string($conn, $_POST['address']);
    $cust_note = mysqli_real_escape_string($conn, $_POST['customer_note']); 
    $order_date = date('Y-m-d H:i:s');

    $sql = "INSERT INTO orders (product_name, description, price, customer_name, contact, address, product_image, order_date, status) 
            VALUES ('$p_name', '$cust_note', '$p_price', '$cust_name', '$cust_contact', '$cust_address', '$p_image', '$order_date', 'Pending')";
    
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | LT SMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #1e293b; 
            --accent: #3b82f6; 
            --success: #10b981;
            --bg: #f8fafc; 
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); margin: 0; padding: 40px 20px; display: flex; justify-content: center; color: #334155; }
        
        .checkout-container { width: 100%; max-width: 550px; }
        .checkout-box { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        
        h2 { margin: 0 0 25px; color: var(--primary); text-align: center; font-size: 1.8rem; }
        
        .product-summary { background: #f1f5f9; padding: 20px; border-radius: 15px; margin-bottom: 30px; display: flex; align-items: center; gap: 20px; border: 1px solid #e2e8f0; }
        .product-summary img { width: 90px; height: 90px; object-fit: cover; border-radius: 12px; background: white; }
        .product-info h3 { margin: 0; font-size: 1.2rem; color: var(--primary); }
        .product-info .price { margin: 5px 0 0; color: var(--success); font-weight: 800; font-size: 1.3rem; }

        .form-group { margin-bottom: 20px; position: relative; }
        .form-group i { position: absolute; left: 18px; top: 18px; color: #94a3b8; font-size: 1.1rem; }
        
        input, textarea { 
            width: 100%; padding: 16px 16px 16px 52px; border: 1px solid #cbd5e1; border-radius: 12px; 
            box-sizing: border-box; font-size: 1rem; transition: all 0.3s ease; font-family: inherit;
        }
        input:focus, textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); outline: none; }
        textarea { height: 100px; resize: none; padding-top: 18px; }

        .confirm-btn { 
            width: 100%; padding: 18px; background: var(--primary); color: white; border: none; 
            border-radius: 12px; cursor: pointer; font-size: 1.1rem; font-weight: bold; 
            margin-top: 10px; transition: transform 0.2s, background 0.2s; 
        }
        .confirm-btn:hover { background: #0f172a; transform: translateY(-2px); }

        .success-card { text-align: center; padding: 20px 0; }
        .success-card i { font-size: 5rem; color: var(--success); margin-bottom: 20px; }
        .cancel-link { display: block; text-align: center; margin-top: 25px; color: #64748b; text-decoration: none; font-size: 0.95rem; font-weight: 600; }
        .cancel-link:hover { color: var(--accent); }
    </style>
</head>
<body>

    <div class="checkout-container">
        <div class="checkout-box">
            <?php if(isset($success)): ?>
                <div class="success-card">
                    <i class="fas fa-check-circle"></i>
                    <h2 style="color: var(--success);">Order Confirmed!</h2>
                    <p style="font-size: 1.1rem; color: #64748b; margin-bottom: 30px;"><?php echo $success; ?></p>
                    <a href="shop.php" class="confirm-btn" style="text-decoration: none; display: block;">Back to Products</a>
                </div>
            <?php else: ?>
                <h2><i class="fas fa-shield-alt" style="color: var(--accent);"></i> Secure Checkout</h2>
                
                <div class="product-summary">
                    <?php $img = !empty($item['image']) ? $item['image'] : 'default.jpg'; ?>
                    <img src="uploads/<?php echo $img; ?>" alt="Product">
                    <div class="product-info">
                        <h3><?php echo $item['name']; ?></h3>
                        <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" placeholder="Full Name" required>
                    </div>
                    
                    <div class="form-group">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="contact" placeholder="Phone or Email" required>
                    </div>
                    
                    <div class="form-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="address" placeholder="Shipping Address" required>
                    </div>
                    
                    <div class="form-group">
                        <i class="fas fa-pen-fancy"></i>
                        <textarea name="customer_note" placeholder="Add specific details or notes for your order (e.g., Color, Size, or Delivery instructions)..." required></textarea>
                    </div>
                    
                    <button type="submit" name="place_order" class="confirm-btn">
                        Complete Order
                    </button>
                </form>
                
                <a href="shop.php" class="cancel-link"><i class="fas fa-arrow-left"></i> Cancel and Return</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>