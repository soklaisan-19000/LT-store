<?php
session_start();
include 'db.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit();
}

$grand_total = 0;
$items_to_buy = [];

// Fetch item details for display from the database based on Session IDs
foreach ($_SESSION['cart'] as $product_id) {
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
    $p = mysqli_fetch_assoc($res);
    if ($p) {
        $items_to_buy[] = $p;
        $grand_total += $p['price'];
    }
}

// Handle Order Submission
if (isset($_POST['place_order'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $order_date = date('Y-m-d H:i:s');

    // Loop through each item in the cart and save to orders table
    foreach ($items_to_buy as $item) {
        $p_name = mysqli_real_escape_string($conn, $item['name']);
        $p_desc = mysqli_real_escape_string($conn, $item['description']); // Grabbing Description
        $p_price = $item['price'];
        $p_img = $item['image'];

        // SQL including the 'description' field
        $sql = "INSERT INTO orders (product_name, description, price, customer_name, contact, product_image, order_date, status) 
                VALUES ('$p_name', '$p_desc', '$p_price', '$customer_name', '$contact', '$p_img', '$order_date', 'Pending')";
        
        mysqli_query($conn, $sql);
    }

    // Clear the cart after successful order
    $_SESSION['cart'] = [];
    header("Location: shop.php?msg=order_success");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Secure Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #2c3e50; 
            --accent: #3498db; 
            --success: #27ae60;
            --bg: #f4f7f6; 
        }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px 5%; color: #333; }
        
        .checkout-container { 
            display: grid; 
            grid-template-columns: 1fr 380px; 
            gap: 30px; 
            max-width: 1100px; 
            margin: 0 auto; 
        }

        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: var(--primary); display: flex; align-items: center; gap: 10px; font-size: 1.4rem; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #7f8c8d; font-size: 0.9rem; }
        .input-group input, .input-group textarea { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1rem; outline: none;
        }
        .input-group input:focus { border-color: var(--accent); }

        /* Summary Item Styles */
        .summary-item { 
            display: flex; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f1f1;
        }
        .summary-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
        .summary-details h4 { margin: 0; font-size: 0.95rem; }
        .summary-details p { margin: 2px 0; font-size: 0.8rem; color: #95a5a6; }

        .total-box { 
            background: #f8fafc; padding: 20px; border-radius: 10px; margin-top: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }

        .btn-confirm { 
            background: var(--success); color: white; width: 100%; border: none; padding: 16px; 
            border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 20px;
        }
        .btn-confirm:hover { background: #219150; transform: translateY(-2px); }

        .back-link { display: block; text-align: center; margin-top: 15px; color: #7f8c8d; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { text-decoration: underline; }

        @media (max-width: 850px) { .checkout-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="card">
        <h2><i class="fas fa-id-card"></i> Customer Information</h2>
        <p style="color:#7f8c8d; font-size: 0.9rem; margin-bottom: 30px;">Complete your details to finish the order.</p>
        
        <form method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your full name" required>
            </div>
            
            <div class="input-group">
                <label>Contact (Phone or Email)</label>
                <input type="text" name="contact" placeholder="How can we reach you?" required>
            </div>
            
            <div class="input-group">
                <label>Delivery Address</label>
                <textarea name="address" rows="3" placeholder="Street name, City, Postcode" required></textarea>
            </div>

            <div style="background: #eef2f7; padding: 15px; border-radius: 8px; border-left: 4px solid var(--accent);">
                <small style="color: #576574;"><strong>Note:</strong> We currently only support Cash on Delivery. Pay when your gadgets arrive!</small>
            </div>

            <button type="submit" name="place_order" class="btn-confirm">
                Place Order ($<?php echo number_format($grand_total, 2); ?>)
            </button>
            <a href="cart.php" class="back-link">Return to Cart</a>
        </form>
    </div>

    <div class="card" style="height: fit-content;">
        <h3 style="margin-top:0;">Order Summary</h3>
        <p style="font-size: 0.85rem; color: #7f8c8d;"><?php echo count($items_to_buy); ?> Items in your cart</p>
        
        <div style="margin-top: 20px;">
            <?php foreach($items_to_buy as $item): ?>
                <div class="summary-item">
                    <img src="uploads/<?php echo $item['image'] ?: 'default.jpg'; ?>" alt="Product">
                    <div class="summary-details">
                        <h4><?php echo $item['name']; ?></h4>
                        <p><?php echo substr($item['description'], 0, 40); ?>...</p>
                        <strong style="color:var(--success);">$<?php echo number_format($item['price'], 2); ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="total-box">
            <span style="font-weight: 600; color: #7f8c8d;">Grand Total</span>
            <span style="font-size: 1.4rem; font-weight: 800; color: var(--primary);">$<?php echo number_format($grand_total, 2); ?></span>
        </div>
    </div>
</div>

</body>
</html>