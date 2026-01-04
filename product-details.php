<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
$product = mysqli_fetch_assoc($query);

if (!$product) { header("Location: shop.php"); exit(); }
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> | LT STORE</title>
    <link rel="shortcut icon" href="image/photo_2025-08-21_13-04-14.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #0f172a; 
            --accent: #3b82f6; 
            --bg: #f1f5f9; 
            --white: #ffffff;
            --text-main: #1e293b;
            --text-light: #64748b;
        }

        body { 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            background-color: var(--bg); 
            color: var(--text-main);
            margin: 0;
            padding: 40px 6%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Animations */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes reveal {
            from { clip-path: inset(0 100% 0 0); }
            to { clip-path: inset(0 0 0 0); }
        }

        .breadcrumb {
            width: 100%;
            max-width: 1100px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            animation: slideInUp 0.5s ease forwards;
        }

        .breadcrumb a {
            text-decoration: none;
            color: var(--text-light);
            transition: 0.3s;
        }

        .breadcrumb a:hover { color: var(--accent); }

        .product-container { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 50px; 
            background: var(--white); 
            padding: 50px; 
            border-radius: 30px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            max-width: 1100px;
            width: 100%;
            box-sizing: border-box;
            animation: slideInUp 0.7s ease-out;
        }

        /* Left Side: Image */
        .img-gallery { 
            position: relative;
            overflow: hidden; 
            border-radius: 20px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-gallery img { 
            width: 100%; 
            height: auto;
            object-fit: cover;
            transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
            animation: reveal 1.2s ease-in-out;
        }

        .img-gallery:hover img { transform: scale(1.08); }

        /* Right Side: Info */
        .info-panel { display: flex; flex-direction: column; justify-content: center; }

        .category-tag {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }

        .product-title { 
            font-size: 2.5rem; 
            margin: 0 0 15px 0; 
            color: var(--primary); 
            line-height: 1.1;
        }

        .price-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .price { font-size: 2.2rem; font-weight: 800; color: var(--accent); }

        .stock-status {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .stock-status.in { background: #dcfce7; color: #166534; }
        .stock-status.out { background: #fee2e2; color: #991b1b; }

        .description { 
            line-height: 1.8; 
            color: var(--text-light); 
            font-size: 1.05rem;
            margin-bottom: 40px; 
            border-left: 3px solid #e2e8f0;
            padding-left: 20px;
        }

        /* Actions */
        .action-area {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .qty-input {
            height: 54px;
            width: 80px;
            padding: 0 15px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            font-weight: 700;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }
        .qty-input:focus { border-color: var(--accent); }

        .btn-main {
            height: 54px;
            padding: 0 35px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-add-cart {
            background: var(--primary);
            color: white;
            flex: 1;
        }
        .btn-add-cart:hover { background: #334155; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15); }

        .btn-buy-now {
            background: var(--accent);
            color: white;
            flex: 1;
        }
        .btn-buy-now:hover { background: #2563eb; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2); }

        .btn-disabled {
            background: #e2e8f0 !important;
            color: #94a3b8 !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: none !important;
        }

        @media (max-width: 900px) {
            .product-container { grid-template-columns: 1fr; padding: 30px; gap: 30px; }
            .product-title { font-size: 2rem; }
        }
    </style>
</head>
<body>

<div class="breadcrumb">
    <a href="shop.php">Shop</a> / <span><?php echo htmlspecialchars($product['name']); ?></span>
</div>

<div class="product-container">
    <div class="img-gallery">
        <img src="uploads/<?php echo !empty($product['image']) ? $product['image'] : 'default.jpg'; ?>" alt="Product Image">
    </div>

    <div class="info-panel">
        <div class="category-tag">Premium Digital</div>
        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <div class="price-wrapper">
            <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
            <?php $stock = (intval($product['stock_qty'] ?? 0) <= 0) ? 'Out of Stock' : (isset($product['stock_status']) ? $product['stock_status'] : 'In Stock'); ?>
            <span class="stock-status <?php echo ($stock=='In Stock' ? 'in' : 'out'); ?>">
                <i class="fas <?php echo ($stock=='In Stock' ? 'fa-check-circle' : 'fa-times-circle'); ?>"></i> 
                <?php echo $stock; ?>
            </span>
            <?php if(isset($_GET['error']) && $_GET['error'] === 'outofstock'): ?>
                <div style="color:#ef4444; font-weight:700; margin-top:8px;">This product is currently out of stock.</div>
            <?php endif; ?>
        </div>

        <p class="description">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </p>

        <div class="action-area">
            <?php if ($stock === 'Out of Stock' || intval($product['stock_qty'] ?? 1) <= 0): ?>
                <button class="btn-main btn-add-cart btn-disabled" disabled><i class="fas fa-cart-plus"></i> Add to Cart</button>
                <button class="btn-main btn-buy-now btn-disabled" disabled>Out of Stock</button>
            <?php else: ?>
                <form action="shop.php" method="GET" style="display:contents;">
                    <input type="hidden" name="add_to_cart" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <input type="number" name="qty" class="qty-input" value="1" min="1" max="<?php echo intval($product['stock_qty'] ?? 99); ?>">
                    
                    <button type="submit" class="btn-main btn-add-cart">
                        <i class="fas fa-shopping-bag"></i> Add to Cart
                    </button>
                    
                    <button type="submit" formaction="checkout.php" formmethod="GET" class="btn-main btn-buy-now">
                        Buy Now
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Subtle Scroll Reveal effect for the info panel
    document.addEventListener('DOMContentLoaded', () => {
        const info = document.querySelector('.info-panel');
        info.style.opacity = '0';
        info.style.transform = 'translateX(20px)';
        
        setTimeout(() => {
            info.style.transition = 'all 0.8s ease-out';
            info.style.opacity = '1';
            info.style.transform = 'translateX(0)';
        }, 300);
    });
</script>

</body>
</html>