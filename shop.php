<?php
session_start();
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");

// 1. Initialize Cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 2. Handle "Add to Cart" logic
if (isset($_GET['add_to_cart'])) {
    $id = $_GET['add_to_cart'];
    $_SESSION['cart'][] = $id;
    header("Location: shop.php?success=added");
    exit();
}

// 3. Fetch all products
$products = mysqli_query($conn, "SELECT * FROM products");
$cart_count = count($_SESSION['cart']);

// Social Links
$tg_link = "https://t.me/ltstore168";
$fb_link = "https://facebook.com/YOUR_PAGE";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Store | Shop Gadgets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e293b;
            --accent: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --bg: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
            --text-muted: #64748b;
        }

        body { 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            margin: 0; 
            background: var(--bg); 
            color: var(--primary); 
        }

        /* Navigation Bar */
        nav {
            background: var(--white);
            padding: 15px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .logo { font-size: 1.6rem; font-weight: 800; color: var(--primary); text-decoration: none; letter-spacing: -1px; }
        .logo span { color: var(--accent); }

        .nav-icons { display: flex; align-items: center; gap: 25px; }
        .cart-link { position: relative; color: var(--primary); text-decoration: none; font-size: 1.3rem; transition: 0.3s; }
        .cart-link:hover { color: var(--accent); }
        .cart-badge { 
            position: absolute; top: -8px; right: -12px; 
            background: var(--danger); color: white; 
            font-size: 0.7rem; font-weight: bold;
            padding: 2px 7px; border-radius: 50%;
            border: 2px solid white;
        }

        .admin-link { text-decoration: none; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; transition: 0.3s; }
        .admin-link:hover { color: var(--primary); }

        /* Hero Header */
        .hero {
            background: linear-gradient(rgba(30, 41, 59, 0.85), rgba(30, 41, 59, 0.85)), url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 5%;
            text-align: center;
        }

        /* Trust Bar */
        .trust-bar {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            padding: 25px 8%;
            background: white;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--border);
            gap: 20px;
        }
        .trust-item { text-align: center; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; }
        .trust-item i { color: var(--accent); font-size: 1.4rem; margin-bottom: 8px; display: block; }

        /* Search Section */
        .search-container {
            max-width: 600px;
            margin: -35px auto 40px;
            position: relative;
            z-index: 10;
        }
        .search-container input {
            width: 100%;
            padding: 18px 25px 18px 55px;
            border-radius: 50px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }
        .search-container input:focus { border-color: var(--accent); box-shadow: 0 10px 25px rgba(59, 130, 246, 0.1); }
        .search-container i { position: absolute; left: 22px; top: 20px; color: var(--text-muted); font-size: 1.1rem; }

        /* Product Grid */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px 60px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px; }

        .card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px rgba(0,0,0,0.06); }

        .img-box { width: 100%; height: 220px; overflow: hidden; background: #f1f5f9; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .card:hover .img-box img { transform: scale(1.08); }

        .card-body { padding: 20px; flex-grow: 1; }
        .card-body h3 { margin: 0 0 10px; font-size: 1.15rem; font-weight: 700; }
        .card-body p { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; height: 42px; overflow: hidden; margin-bottom: 15px; }
        .price-tag { font-size: 1.3rem; font-weight: 800; color: var(--success); }

        .card-footer { padding: 0 20px 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn { text-decoration: none; padding: 11px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; text-align: center; transition: 0.3s; }
        
        .btn-cart { background: #f1f5f9; color: var(--primary); }
        .btn-cart:hover { background: var(--border); }
        .btn-buy { background: var(--accent); color: white; }
        .btn-buy:hover { background: #2563eb; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }

        /* Notification */
        .alert {
            position: fixed; top: 100px; right: 20px;
            background: var(--success); color: white;
            padding: 15px 25px; border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            z-index: 2000;
            display: none;
            font-weight: 600;
        }

        /* Footer Section */
        footer { background: var(--primary); color: white; padding: 60px 8% 30px; margin-top: 50px; text-align: center; }
        .footer-socials { margin: 25px 0; display: flex; justify-content: center; gap: 25px; }
        .footer-socials a { color: white; font-size: 1.6rem; transition: 0.3s; opacity: 0.8; }
        .footer-socials a:hover { color: var(--accent); opacity: 1; transform: translateY(-3px); }
        
        /* Floating Telegram Button */
        .float-contact {
            position: fixed; bottom: 30px; right: 30px;
            background: #0088cc; color: white;
            width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; box-shadow: 0 10px 20px rgba(0,136,204,0.3);
            text-decoration: none; z-index: 999; transition: 0.3s;
        }
        .float-contact:hover { transform: scale(1.1); background: #0099e6; }

        @media (max-width: 768px) {
            .trust-bar { justify-content: center; }
            .trust-item { width: 45%; }
        }
    </style>
</head>
<body>

<a href="<?php echo $tg_link; ?>" class="float-contact" target="_blank" title="Chat on Telegram">
    <i class="fab fa-telegram-plane"></i>
</a>

<nav>
    <a href="shop.php" class="logo">LT-<span>STORE</span></a>
    <div class="nav-icons">
        <a href="index.php" class="admin-link">STAFF ACCESS</a>
        <a href="cart.php" class="cart-link">
            <i class="fas fa-shopping-basket"></i>
            <?php if($cart_count > 0): ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
    </div>
</nav>

<div class="hero">
    <h1 style="margin:0; font-size: 2.8rem; font-weight: 800; letter-spacing: -1px;">Buy Evrthing on LT-STORE</h1>
    <p style="opacity: 0.9; font-size: 1.1rem; margin-top: 10px;">Premium quality digital products with instant delivery.</p>
</div>

<div class="trust-bar">
    <div class="trust-item"><i class="fas fa-bolt"></i> Instant Delivery</div>
    <div class="trust-item"><i class="fas fa-shield-check"></i> Secure Checkout</div>
    <div class="trust-item"><i class="fas fa-headset"></i> 24/7 Support</div>
    <div class="trust-item"><i class="fas fa-award"></i> Quality Guaranteed</div>
</div>

<div class="container">
    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" id="productSearch" placeholder="Search for products, keyboards, accounts..." onkeyup="searchFunction()">
    </div>

    <div class="product-grid" id="productGrid">
        <?php if(mysqli_num_rows($products) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($products)): ?>
                <div class="card product-item">
                    <div class="img-box">
                        <?php $img = !empty($row['image']) ? $row['image'] : 'default.jpg'; ?>
                        <img src="uploads/<?php echo $img; ?>" alt="Product">
                    </div>
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <div class="price-tag">$<?php echo number_format($row['price'], 2); ?></div>
                    </div>
                    <div class="card-footer">
                        <a href="shop.php?add_to_cart=<?php echo $row['id']; ?>" class="btn btn-cart">
                            <i class="fas fa-cart-plus"></i> Cart
                        </a>
                        <a href="checkout.php?id=<?php echo $row['id']; ?>" class="btn btn-buy">Buy Now</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-muted);">
                <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Our catalog is currently being updated. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    <div class="logo" style="color: white; margin-bottom: 15px;">LT<span style="color: var(--accent);">STORE</span></div>
    <p style="opacity: 0.6; font-size: 0.95rem; max-width: 500px; margin: 0 auto;">Providing the community with the best digital tools and hardware solutions since 2024.</p>
    
    <div class="footer-socials">
        <a href="<?php echo $tg_link; ?>" target="_blank"><i class="fab fa-telegram"></i></a>
        <a href="<?php echo $fb_link; ?>" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-discord"></i></a>
    </div>
    
    <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px; font-size: 0.85rem; opacity: 0.5;">
        &copy; <?php echo date('Y'); ?> LT SMS STORE. All rights reserved.
    </div>
</footer>

<?php if(isset($_GET['success'])): ?>
    <div class="alert" id="successAlert">✓ Item added to your cart!</div>
    <script>
        const alertBox = document.getElementById('successAlert');
        alertBox.style.display = 'block';
        setTimeout(() => {
            alertBox.style.opacity = '0';
            alertBox.style.transition = 'opacity 0.5s ease';
            setTimeout(() => { alertBox.style.display = 'none'; }, 500);
        }, 3000);
    </script>
<?php endif; ?>

<script>
    function searchFunction() {
        let input = document.getElementById('productSearch').value.toUpperCase();
        let grid = document.getElementById('productGrid');
        let items = grid.getElementsByClassName('product-item');

        for (let i = 0; i < items.length; i++) {
            let title = items[i].querySelector('h3').innerText;
            if (title.toUpperCase().indexOf(input) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }
</script>

</body>
</html>