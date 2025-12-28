<?php
session_start();
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");

// 1. Initialize Cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 2. Handle "Add to Cart" logic
if (isset($_GET['add_to_cart'])) {
    $id = mysqli_real_escape_string($conn, $_GET['add_to_cart']);
    $_SESSION['cart'][] = $id;
    header("Location: shop.php?success=added");
    exit();
}

// 3. Fetch products
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$cart_count = count($_SESSION['cart']);

$tg_link = "https://t.me/ltstore168";
$fb_link = "https://facebook.com/YOUR_PAGE";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LT STORE | Premium Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e293b; --accent: #3b82f6; --success: #10b981;
            --danger: #ef4444; --bg: #f8fafc; --white: #ffffff;
            --border: #e2e8f0; --text-muted: #64748b;
        }

        body { 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            margin: 0; background: var(--bg); color: var(--primary); 
            -webkit-font-smoothing: antialiased;
        }

        /* Top Social Bar */
        .top-bar {
            background: var(--primary); color: white;
            padding: 8px 6%; display: flex; justify-content: space-between;
            align-items: center; font-size: 0.75rem;
        }
        .top-bar a { color: white; text-decoration: none; margin-left: 15px; opacity: 0.8; transition: 0.3s; }
        .top-bar a:hover { opacity: 1; color: var(--accent); }

        /* Compact Navigation */
        nav {
            background: var(--white); padding: 10px 6%; display: flex;
            justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .logo { font-size: 1.3rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { color: var(--accent); }
        .nav-icons { display: flex; align-items: center; gap: 20px; }
        
        .staff-link { text-decoration: none; color: var(--text-muted); font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .staff-link:hover { color: var(--accent); }

        .cart-link { position: relative; color: var(--primary); text-decoration: none; font-size: 1.2rem; }
        .cart-badge { 
            position: absolute; top: -8px; right: -10px; 
            background: var(--danger); color: white; font-size: 0.65rem; 
            padding: 1px 6px; border-radius: 50%; border: 2px solid white;
        }

        /* Compact Hero */
        .hero {
            background: linear-gradient(rgba(30, 41, 59, 0.9), rgba(30, 41, 59, 0.9)), url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80');
            background-size: cover; background-position: center; color: white; 
            padding: 40px 5%; text-align: center;
        }
        .hero h1 { margin: 0; font-size: 1.8rem; letter-spacing: -0.5px; }
        .hero p { margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem; }

        /* Search Bar */
        .search-container { max-width: 500px; margin: -22px auto 30px; position: relative; z-index: 10; }
        .search-container input {
            width: 100%; padding: 12px 20px 12px 45px; border-radius: 50px;
            border: 1px solid var(--border); box-shadow: 0 8px 20px rgba(0,0,0,0.06); outline: none;
            font-size: 0.9rem; box-sizing: border-box;
        }
        .search-container i { position: absolute; left: 18px; top: 14px; color: var(--text-muted); }

        /* Grid System */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px 50px; }
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
            gap: 15px; 
        }

        /* Product Cards */
        .card { 
            background: var(--white); border-radius: 12px; overflow: hidden; 
            transition: 0.25s; border: 1px solid var(--border); 
            display: flex; flex-direction: column;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.08); }
        .img-box { width: 100%; height: 140px; overflow: hidden; background: #f1f5f9; cursor: pointer; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .img-box:hover img { transform: scale(1.05); } /* Subtle zoom on image click/hover */
        
        .card-body { padding: 12px; flex-grow: 1; }
        .card-body h3 { margin: 0 0 4px; font-size: 0.95rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-body p { font-size: 0.75rem; color: var(--text-muted); line-height: 1.3; height: 30px; overflow: hidden; margin-bottom: 8px; }
        .price-tag { font-size: 1.1rem; font-weight: 800; color: var(--primary); }

        .card-footer { padding: 0 12px 12px; display: grid; grid-template-columns: 40px 1fr; gap: 8px; }
        .btn { text-decoration: none; padding: 8px 0; border-radius: 8px; font-weight: 700; font-size: 0.75rem; text-align: center; border: none; cursor: pointer; }
        .btn-cart { background: #f1f5f9; color: var(--primary); }
        .btn-buy { background: var(--accent); color: white; }

        /* Enhanced Footer */
        footer { background: var(--primary); color: white; padding: 50px 6% 20px; margin-top: 50px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 30px; }
        .footer-col h4 { color: white; margin-bottom: 15px; font-size: 1rem; }
        .footer-col p { color: #94a3b8; font-size: 0.85rem; line-height: 1.6; }
        .footer-socials { display: flex; gap: 15px; margin-top: 15px; }
        .footer-socials a { color: white; background: rgba(255,255,255,0.1); width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; text-decoration: none; }
        .footer-socials a:hover { background: var(--accent); transform: translateY(-3px); }
        .copyright { text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; font-size: 0.75rem; color: #64748b; }

        /* Utilities */
        .float-contact {
            position: fixed; bottom: 25px; right: 25px;
            background: #0088cc; color: white; width: 50px; height: 50px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; box-shadow: 0 8px 15px rgba(0,136,204,0.3); text-decoration: none; z-index: 999;
        }
        .alert { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: var(--primary); color: white; padding: 10px 25px; border-radius: 50px; font-size: 0.85rem; z-index: 2000; display: none; }
    </style>
</head>
<body>

<div class="top-bar">
    <div>Welcome to LT-Store Premium Digital Solutions</div>
    <div>
        <a href="<?php echo $tg_link; ?>" target="_blank"><i class="fab fa-telegram"></i> Telegram</a>
        <a href="<?php echo $fb_link; ?>" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
    </div>
</div>

<nav>
    <a href="shop.php" class="logo">LT<span>STORE</span></a>
    <div class="nav-icons">
        <a href="index.php" class="staff-link"><i class="fas fa-user-shield"></i> STAFF ACCESS</a>
        <a href="cart.php" class="cart-link">
            <i class="fas fa-shopping-basket"></i>
            <?php if($cart_count > 0): ?><span class="cart-badge"><?php echo $cart_count; ?></span><?php endif; ?>
        </a>
    </div>
</nav>

<div class="hero">
    <h1>LT-STORE PREMIUM</h1>
    <p>Instant Digital Delivery • 24/7 Support</p>
</div>

<div class="container">
    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" id="productSearch" placeholder="Search products..." onkeyup="searchFunction()">
    </div>

    <div class="product-grid" id="productGrid">
        <?php if(mysqli_num_rows($products) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($products)): ?>
                <div class="card product-item">
                    <a href="product-details.php?id=<?php echo $row['id']; ?>" class="img-box">
                        <?php $img = !empty($row['image']) ? $row['image'] : 'default.jpg'; ?>
                        <img src="uploads/<?php echo $img; ?>" alt="Product">
                    </a>
                    
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <div class="price-tag">$<?php echo number_format($row['price'], 2); ?></div>
                    </div>

                    <div class="card-footer">
                        <a href="shop.php?add_to_cart=<?php echo $row['id']; ?>" class="btn btn-cart" title="Add to Cart">
                            <i class="fas fa-cart-plus"></i>
                        </a>
                        <a href="checkout.php?id=<?php echo $row['id']; ?>" class="btn btn-buy">Buy Now</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<a href="<?php echo $tg_link; ?>" class="float-contact" target="_blank"><i class="fab fa-telegram-plane"></i></a>

<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <h4>About LT-STORE</h4>
            <p>Your number one source for all things digital. We're dedicated to giving you the very best of digital products, with a focus on reliability and customer service.</p>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <p><a href="shop.php" style="color:#94a3b8; text-decoration:none;">Shop All</a><br>
            <a href="cart.php" style="color:#94a3b8; text-decoration:none;">My Basket</a><br>
            <a href="index.php" style="color:#94a3b8; text-decoration:none;">Staff Portal</a></p>
        </div>
        <div class="footer-col">
            <h4>Connect With Us</h4>
            <div class="footer-socials">
                <a href="<?php echo $tg_link; ?>" target="_blank"><i class="fab fa-telegram"></i></a>
                <a href="<?php echo $fb_link; ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-discord"></i></a>
            </div>
        </div>
    </div>
    <div class="copyright">
        &copy; <?php echo date('Y'); ?> LT STORE. All rights reserved. Designed for Premium Quality.
    </div>
</footer>

<div class="alert" id="successAlert">✓ Added to cart</div>

<script>
    function searchFunction() {
        let input = document.getElementById('productSearch').value.toUpperCase();
        let items = document.getElementsByClassName('product-item');
        for (let i = 0; i < items.length; i++) {
            let title = items[i].querySelector('h3').innerText;
            items[i].style.display = title.toUpperCase().indexOf(input) > -1 ? "" : "none";
        }
    }
    <?php if(isset($_GET['success'])): ?>
        const alertBox = document.getElementById('successAlert');
        alertBox.style.display = 'block';
        setTimeout(() => { alertBox.style.display = 'none'; }, 3000);
    <?php endif; ?>
</script>

</body>
</html>