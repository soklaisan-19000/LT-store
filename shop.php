<?php
session_start();
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");

// 1. Initialize Cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 2. Handle "Add to Cart" logic (Unique ID storage)
if (isset($_GET['add_to_cart'])) {
    $id = $_GET['add_to_cart'];
    $_SESSION['cart'][] = $id;
    // Redirect to prevent duplicate adding on refresh
    header("Location: shop.php?success=added");
    exit();
}

// 3. Fetch all products
$products = mysqli_query($conn, "SELECT * FROM products");
$cart_count = count($_SESSION['cart']);
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
            --primary: #2c3e50;
            --accent: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --bg: #f4f7f6;
            --white: #ffffff;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            background: var(--bg); 
            color: #333; 
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

        .admin-link { text-decoration: none; color: #7f8c8d; font-size: 0.85rem; font-weight: 600; }

        /* Hero Header */
        .hero {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 5%;
            text-align: center;
        }

        /* Search Section */
        .search-container {
            max-width: 600px;
            margin: -30px auto 40px;
            position: relative;
        }

        .search-container input {
            width: 100%;
            padding: 16px 25px 16px 50px;
            border-radius: 50px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            font-size: 1rem;
            outline: none;
        }

        .search-container i {
            position: absolute; left: 20px; top: 18px;
            color: #95a5a6; font-size: 1.1rem;
        }

        /* Product Grid */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px 60px; }
        
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 30px; 
        }

        /* Product Card Style */
        .card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }

        .img-box { width: 100%; height: 230px; overflow: hidden; background: #f8f9fa; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .card:hover .img-box img { transform: scale(1.1); }

        .card-body { padding: 20px; flex-grow: 1; text-align: left; }
        .card-body h3 { margin: 0 0 8px; font-size: 1.2rem; color: var(--primary); }
        .card-body p { font-size: 0.9rem; color: #7f8c8d; line-height: 1.4; height: 40px; overflow: hidden; margin-bottom: 15px; }
        
        .price-tag { font-size: 1.4rem; font-weight: 800; color: var(--success); }

        /* Buttons */
        .card-footer { padding: 0 20px 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        
        .btn {
            text-decoration: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            text-align: center;
            transition: 0.3s;
        }

        .btn-cart { background: #f1f2f6; color: var(--primary); }
        .btn-cart:hover { background: #dfe4ea; }

        .btn-buy { background: var(--accent); color: white; }
        .btn-buy:hover { background: #2980b9; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3); }

        /* Notification */
        .alert {
            position: fixed; top: 90px; right: 20px;
            background: var(--success); color: white;
            padding: 12px 25px; border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 2000;
            display: none;
            animation: slideIn 0.5s forwards;
        }

        @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }

    </style>
</head>
<body>

<nav>
    <a href="shop.php" class="logo">LT<span>STORE</span></a>
    
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
    <h1 style="margin:0; font-size: 2.5rem;">Discover Next-Gen Tech</h1>
    <p style="opacity: 0.8;">Premium products, unbeatable prices, and lightning-fast delivery.</p>
</div>

<div class="container">
    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" id="productSearch" placeholder="What are you looking for?" onkeyup="searchFunction()">
    </div>

    <div class="product-grid" id="productGrid">
        <?php if(mysqli_num_rows($products) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($products)): ?>
                <div class="card product-item">
                    <div class="img-box">
                        <?php $img = !empty($row['image']) ? $row['image'] : 'default.jpg'; ?>
                        <img src="uploads/<?php echo $img; ?>" alt="Product Image">
                    </div>
                    <div class="card-body">
                        <h3><?php echo $row['name']; ?></h3>
                        <p><?php echo $row['description']; ?></p>
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
            <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #95a5a6;">No products available in the catalog yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert" id="successAlert">✓ Item added to your cart!</div>
    <script>
        document.getElementById('successAlert').style.display = 'block';
        setTimeout(() => { document.getElementById('successAlert').style.fadeOut(); }, 3000);
    </script>
<?php endif; ?>

<script>
    // Live Search Function
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