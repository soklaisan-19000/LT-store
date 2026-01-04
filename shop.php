<?php
session_start();
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");

// 1. Initialize Cart (associative: product_id => qty)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 2. Handle "Add to Cart" logic (supports qty param)
if (isset($_GET['add_to_cart'])) {
    $id = intval($_GET['add_to_cart']);
    $qty = isset($_GET['qty']) ? max(1, intval($_GET['qty'])) : 1;

    // Check stock quantity
    $res_check = mysqli_query($conn, "SELECT stock_qty, stock_status FROM products WHERE id='$id' LIMIT 1");
    $r = mysqli_fetch_assoc($res_check);
    if (!$r) {
        header("Location: shop.php?error=notfound"); exit();
    }
    if (isset($r['stock_status']) && $r['stock_status'] === 'Out of Stock') {
        header("Location: shop.php?error=outofstock");
        exit();
    }
    $available = isset($r['stock_qty']) ? intval($r['stock_qty']) : 0;
    $current = isset($_SESSION['cart'][$id]) ? intval($_SESSION['cart'][$id]) : 0;
    if ($qty + $current > $available) {
        header("Location: shop.php?error=insufficient&available=$available"); exit();
    }

    // Add to cart
    $_SESSION['cart'][$id] = $current + $qty;
    header("Location: shop.php?success=added");
    exit();
}

// 3. Fetch products (include stock_qty)
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$cart_count = array_sum($_SESSION['cart']);

$tg_link = "https://t.me/ltstore168";
$fb_link = "https://facebook.com/YOUR_PAGE";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="image/photo_2025-08-21_13-04-14.jpg" type="image/x-icon">
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

        /* --- NEW ANIMATIONS --- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            from { transform: translateX(100%) translateX(-50%); opacity: 0; }
            to { transform: translateX(-50%); opacity: 1; }
        }

        @keyframes pulseCart {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
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
            animation: pulseCart 0.5s ease-in-out;
        }

        /* Compact Hero */
        .hero {
            background: linear-gradient(rgba(30, 41, 59, 0.9), rgba(30, 41, 59, 0.9)), url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80');
            background-size: cover; background-position: center; color: white; 
            padding: 40px 5%; text-align: center;
        }
        .hero h1 { margin: 0; font-size: 1.8rem; letter-spacing: -0.5px; animation: fadeInUp 0.8s ease-out; }
        .hero p { margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem; animation: fadeInUp 1s ease-out; }

        /* Search Bar */
        .search-container { max-width: 500px; margin: -22px auto 30px; position: relative; z-index: 10; }
        .search-container input {
            width: 100%; padding: 12px 20px 12px 45px; border-radius: 50px;
            border: 1px solid var(--border); box-shadow: 0 8px 20px rgba(0,0,0,0.06); outline: none;
            font-size: 0.9rem; box-sizing: border-box; transition: 0.3s;
        }
        .search-container input:focus { border-color: var(--accent); box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2); }
        .search-container i { position: absolute; left: 18px; top: 14px; color: var(--text-muted); }

        /* Grid System */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px 50px; }
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
            gap: 15px; 
        }

        /* Product Cards with Animation */
        .card { 
            background: var(--white); border-radius: 12px; overflow: hidden; 
            transition: all 0.3s ease; border: 1px solid var(--border); 
            display: flex; flex-direction: column;
            animation: fadeInUp 0.6s ease-out backwards;
        }
        .card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.12); }
        .img-box { position: relative; width: 100%; height: 140px; overflow: hidden; background: #f1f5f9; cursor: pointer; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; display:block; }
        .img-box:hover img { transform: scale(1.1); } 
        /* Out-of-stock visual treatment */
        .card.out-of-stock { border-color: #fee2e2; filter: grayscale(40%); opacity: 0.95; }
        .card.out-of-stock .price-tag { color: #b91c1c; opacity: 0.95; }
        .status-top { position: absolute; top: 8px; left: 8px; z-index: 3; }
        .card .status-top .stock-badge { font-weight: 800; font-size: 0.8rem; padding: 4px 8px; }
        .card.out-of-stock .status-top .stock-badge { background: #fee2e2; color: #991b1b; }
        .card.out-of-stock .add-to-cart-form { display: none; } 
        
        .card-body { padding: 12px; flex-grow: 1; }
        .card-body h3 { margin: 0 0 4px; font-size: 0.95rem; font-weight: 700; }
        .card-body p { font-size: 0.75rem; color: var(--text-muted); line-height: 1.3; height: 30px; overflow: hidden; margin-bottom: 8px; }
        .price-tag { font-size: 1.1rem; font-weight: 800; color: var(--primary); }

        .card-footer { padding: 6px 12px 12px; display:flex; gap:8px; align-items:center; justify-content:center; }
        .btn { text-decoration: none; padding: 6px 8px; border-radius: 8px; font-weight: 700; font-size: 0.75rem; text-align: center; border: none; cursor: pointer; transition: 0.12s; }
        .btn-cart { background: #f1f5f9; color: var(--primary); }
        .btn-cart:hover { background: var(--primary); color: white; }
        .btn-buy { background: #c40000ff; color: white; }
        .btn-buy:hover { background: #a00000; }
        /* Compact quantity and action styles */
        .qty-input { width: 60px; padding: 6px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.85rem; }
        .btn-add { background: linear-gradient(90deg,#06b6d4,#3b82f6); color: white; padding: 8px 10px; border-radius: 8px; border: none; cursor: pointer; font-weight:800; display:inline-flex; gap:6px; align-items:center; box-shadow: 0 6px 12px rgba(59,130,246,0.14); font-size:0.85rem; }
        .btn-add i { font-size: 0.85rem; }
        .btn-add:hover { transform: translateY(-1px); }
        .btn-buy { background: linear-gradient(90deg,#ef4444,#c40000); color:white; padding:8px 10px; border-radius: 8px; border:none; cursor:pointer; font-weight:800; box-shadow: 0 6px 12px rgba(220,38,38,0.14); font-size:0.85rem; }
        .btn-buy i { margin-right:6px; }
        .btn-buy:hover { transform: translateY(-1px); filter: brightness(0.98); }
        .card-footer .add-to-cart-form { gap: 8px; display:flex; align-items:center; }
        @media (max-width: 480px) { .card-footer .add-to-cart-form { flex-direction: column; align-items: stretch; } .qty-input{ width:100%; } .btn-add, .btn-buy { width:100%; } }

        footer { background: var(--primary); color: white; padding: 50px 6% 20px; margin-top: 50px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 30px; }
        .footer-col h4 { color: white; margin-bottom: 15px; font-size: 1rem; }
        .footer-col p { color: #94a3b8; font-size: 0.85rem; line-height: 1.6; }
        .footer-socials { display: flex; gap: 15px; margin-top: 15px; }
        .footer-socials a { color: white; background: rgba(255,255,255,0.1); width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; text-decoration: none; }
        .footer-socials a:hover { background: var(--accent); transform: translateY(-3px); }
        .copyright { text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; font-size: 0.75rem; color: #64748b; }

        .float-contact {
            position: fixed; bottom: 25px; right: 25px;
            background: #0088cc; color: white; width: 50px; height: 50px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; box-shadow: 0 8px 15px rgba(0,136,204,0.3); text-decoration: none; z-index: 999;
            transition: 0.3s;
        }
        .float-contact:hover { transform: scale(1.1); }

        /* Alert Animation Style */
        .alert { 
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); 
            background: var(--primary); color: white; padding: 12px 30px; 
            border-radius: 50px; font-size: 0.85rem; z-index: 2000; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            display: none; 
        }
        /* Stock badge */
        .stock-badge { padding: 6px 10px; border-radius: 20px; font-size: 0.75rem; font-weight:700; }
        .stock-badge.in { background: #d1fae5; color: #065f46; }
        .stock-badge.out { background: #fee2e2; color: #991b1b; }
        .btn-disabled { opacity: 1; cursor: not-allowed; }
        .error-alert { background: #ef4444; }
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
        <a href="index.php" class="staff-link"><i class="fas fa-user-shield"></i> ONLY ADMIN ACCESS</a>
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
        <?php 
        $delay = 0;
        if(mysqli_num_rows($products) > 0): 
            while($row = mysqli_fetch_assoc($products)): 
                $delay += 0.05; // Incremental delay for staggered animation
                $stock_qty_val = intval($row['stock_qty'] ?? 0);
                $status = ($stock_qty_val <= 0) ? 'Out of Stock' : (isset($row['stock_status']) ? $row['stock_status'] : 'In Stock');
        ?>
                <div class="card product-item <?php echo ($status === 'Out of Stock' || intval($row['stock_qty']) <= 0) ? 'out-of-stock' : ''; ?>" style="animation-delay: <?php echo $delay; ?>s;">
                    <a href="product-details.php?id=<?php echo $row['id']; ?>" class="img-box">
                        <?php $img = !empty($row['image']) ? $row['image'] : 'default.jpg'; ?>
                        <img src="uploads/<?php echo $img; ?>" alt="Product">
                        <div class="status-top"><span class="stock-badge <?php echo ($status=='In Stock' ? 'in' : 'out'); ?>"><?php echo $status; ?></span></div>
                    </a>
                    
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                            <div class="price-tag">$<?php echo number_format($row['price'], 2); ?></div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <?php if ($status === 'Out of Stock' || intval($row['stock_qty']) <= 0): ?>
                            <div style="width:100%;"></div>
                        <?php else: ?>
                            <form method="GET" class="add-to-cart-form" style="display:flex; gap:8px; align-items:center; width:auto;">
                                <input type="hidden" name="add_to_cart" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="number" name="qty" value="1" min="1" max="<?php echo intval($row['stock_qty']); ?>" class="qty-input" aria-label="Quantity">
                                <button type="submit" class="btn btn-add" title="Add to Cart"><i class="fas fa-cart-plus"></i></button>
                                <button type="submit" formaction="checkout.php" formmethod="GET" class="btn btn-buy" title="Buy Now"><i class="fas fa-bolt"></i></button>
                            </form>
                        <?php endif; ?>
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
            <p>Your number one source for all things digital. We're dedicated to giving you the very best of digital products.</p>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <p><a href="shop.php" style="color:#94a3b8; text-decoration:none;">Shop All</a><br>
            <a href="cart.php" style="color:#94a3b8; text-decoration:none;">My Basket</a></p>
        </div>
        <div class="footer-col">
            <h4>Connect With Us</h4>
            <div class="footer-socials">
                <a href="<?php echo $tg_link; ?>"><i class="fab fa-telegram"></i></a>
                <a href="<?php echo $fb_link; ?>"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>
    </div>
    <div class="copyright">
        &copy; <?php echo date('Y'); ?> LT STORE. All rights reserved.
    </div>
</footer>

<div class="alert" id="successAlert">✓ Added to cart</div>
<div class="alert" id="errorAlert">Out of stock</div>

<script>
    // 1. Improved Search with Fade Animation
    function searchFunction() {
        let input = document.getElementById('productSearch').value.toUpperCase();
        let items = document.getElementsByClassName('product-item');
        
        for (let i = 0; i < items.length; i++) {
            let title = items[i].querySelector('h3').innerText;
            if (title.toUpperCase().indexOf(input) > -1) {
                items[i].style.display = "";
                items[i].style.opacity = "1";
                items[i].style.transform = "scale(1)";
            } else {
                items[i].style.opacity = "0";
                items[i].style.transform = "scale(0.9)";
                setTimeout(() => { 
                    if(items[i].style.opacity === "0") items[i].style.display = "none"; 
                }, 300);
            }
        }
    }

    // 2. Animated Alert Box
    <?php if(isset($_GET['success'])): ?>
        const alertBox = document.getElementById('successAlert');
        alertBox.style.display = 'block';
        alertBox.animate([
            { transform: 'translate(-50%, 50px)', opacity: 0 },
            { transform: 'translate(-50%, 0)', opacity: 1 }
        ], { duration: 500, easing: 'ease-out' });

        setTimeout(() => {
            alertBox.animate([
                { opacity: 1 },
                { opacity: 0 }
            ], { duration: 500 }).onfinish = () => alertBox.style.display = 'none';
        }, 3000);
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] === 'outofstock'): ?>
        const errBox = document.getElementById('errorAlert');
        errBox.style.display = 'block';
        errBox.classList.add('error-alert');
        errBox.animate([
            { transform: 'translate(-50%, 50px)', opacity: 0 },
            { transform: 'translate(-50%, 0)', opacity: 1 }
        ], { duration: 500, easing: 'ease-out' });

        setTimeout(() => {
            errBox.animate([
                { opacity: 1 },
                { opacity: 0 }
            ], { duration: 500 }).onfinish = () => errBox.style.display = 'none';
        }, 3000);
    <?php endif; ?>

    // Disable add/buy controls on products with zero stock (defensive client-side check)
    (function(){
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            const qtyInput = form.querySelector('input[name="qty"]');
            const addBtn = form.querySelector('button.btn-add');
            const buyBtn = form.querySelector('button.btn-buy');
            if (!qtyInput) return;
            const max = parseInt(qtyInput.getAttribute('max') || '0');
            if (max <= 0) {
                qtyInput.value = 0;
                qtyInput.disabled = true;
                if (addBtn) addBtn.disabled = true;
                if (buyBtn) buyBtn.disabled = true;
                form.style.opacity = '0.6';
                // add an accessible label
                if (!form.querySelector('.out-note')) {
                    const n = document.createElement('div');
                    n.className = 'out-note';
                    n.style.fontSize = '0.85rem'; n.style.color = '#991b1b'; n.style.fontWeight = '700'; n.style.marginLeft = '8px';
                    n.textContent = 'Out of stock';
                    form.appendChild(n);
                }
            }
        });
    })();
</script>

</body>
</html>