<?php
session_start();
include 'db.php'; // Ensure this file has your $conn connection

// 1. Handle Removing an Item from Cart (by product id)
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    header("Location: cart.php?msg=removed");
    exit();
}

// 2. Handle Clearing the whole Cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}

// 3. Update quantity for an item
if (isset($_POST['update_qty'])) {
    $product_id = intval($_POST['product_id']);
    $qty = max(1, intval($_POST['quantity']));
    // validate stock
    $res = mysqli_query($conn, "SELECT stock_qty FROM products WHERE id='$product_id' LIMIT 1");
    $r = mysqli_fetch_assoc($res);
    $available = isset($r['stock_qty']) ? intval($r['stock_qty']) : 0;
    if ($qty > $available) {
        header("Location: cart.php?error=insufficient&available=$available"); exit();
    }
    $_SESSION['cart'][$product_id] = $qty;
    header("Location: cart.php?msg=updated");
    exit();
}
$grand_total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart | LT-STORE</title>
    <link rel="shortcut icon" href="image/photo_2025-08-21_13-04-14.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --bg: #f4f7f6;
        }

        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px 5%; color: #333; }
        
        .cart-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f9f9f9;
        }

        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            background: #eee;
        }

        .item-info { flex-grow: 1; margin-left: 20px; }
        .item-info h3 { margin: 0; font-size: 1.1rem; color: var(--primary); }
        .item-info p { margin: 5px 0 0; color: var(--success); font-weight: bold; }

        .remove-btn {
            color: #bdc3c7;
            text-decoration: none;
            font-size: 1.2rem;
            transition: 0.3s;
            padding: 10px;
        }
        .remove-btn:hover { color: var(--danger); }

        .cart-summary {
            margin-top: 30px;
            background: #f8fafc;
            padding: 25px;
            border-radius: 12px;
            text-align: right;
        }

        .total-price { font-size: 1.8rem; font-weight: 800; color: var(--primary); display: block; margin-bottom: 20px; }

        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-checkout { background: var(--accent); color: white; border: none; font-size: 1rem; cursor: pointer; }
        .btn-checkout:hover { background: #2980b9; transform: translateY(-2px); }

        .btn-back { color: var(--primary); margin-right: 15px; }

        .empty-state { text-align: center; padding: 60px 0; }
        .empty-state i { font-size: 4rem; color: #dfe6e9; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="cart-container">
    <div class="cart-header">
        <h2 style="margin:0;"><i class="fas fa-shopping-cart" style="color:var(--accent);"></i> Your Shopping Cart</h2>
        <?php if(!empty($_SESSION['cart'])): ?>
            <a href="cart.php?clear=1" style="color:var(--danger); text-decoration:none; font-size:0.85rem; font-weight:bold;">CLEAR CART</a>
        <?php endif; ?>
    </div>

    <?php if(empty($_SESSION['cart'])): ?>
        <div class="empty-state">
            <i class="fas fa-shopping-basket"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything yet.</p>
            <br>
            <a href="shop.php" class="btn btn-checkout">Start Shopping</a>
        </div>
    <?php else: ?>
        
        <div class="cart-items-list">
            <?php 
            foreach($_SESSION['cart'] as $product_id => $qty): 
                // Fetch product details for each ID in the cart
                $safe_id = intval($product_id);
                $res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$safe_id'");
                $p = mysqli_fetch_assoc($res);
                if($p):
                    $line_total = $p['price'] * $qty;
                    $grand_total += $line_total;
            ?>
                <div class="cart-item">
                    <img src="uploads/<?php echo $p['image'] ?: 'default.jpg'; ?>" class="item-img">
                    <div class="item-info">
                        <h3><?php echo $p['name']; ?></h3>
                        <p>$<?php echo number_format($p['price'], 2); ?> × <?php echo $qty; ?> = $<?php echo number_format($line_total, 2); ?></p>
                        <form method="POST" style="margin-top:8px; display:flex; gap:8px; align-items:center;">
                            <input type="hidden" name="product_id" value="<?php echo $safe_id; ?>">
                            <input type="number" name="quantity" value="<?php echo $qty; ?>" min="1" max="<?php echo intval($p['stock_qty']); ?>" style="width:80px; padding:6px; border-radius:6px; border:1px solid #e2e8f0;">
                            <button type="submit" name="update_qty" class="btn btn-checkout" style="padding:8px 12px;">Update</button>
                        </form>
                    </div>
                    <a href="cart.php?remove=<?php echo $safe_id; ?>" class="remove-btn" title="Remove Item">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>

        <div class="cart-summary">
            <span style="color:#7f8c8d; font-weight:600;">Subtotal</span>
            <span class="total-price">$<?php echo number_format($grand_total, 2); ?></span>
            
            <div style="display:flex; justify-content: flex-end; align-items:center; gap:8px;">
                <a href="shop.php" class="btn-back"><i class="fas fa-arrow-left"></i> Keep Shopping</a>
                <a href="checkout_multi.php" class="btn btn-checkout">Checkout Now</a>
            </div>
        </div>

    <?php endif; ?>
</div>

</body>
</html>