<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
$product = mysqli_fetch_assoc($query);

if (!$product) { header("Location: shop.php"); exit(); }
$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product['name']; ?> | LT STORE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; --bg: #f8fafc; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); padding: 50px 6%; margin:0; }
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--accent); text-decoration: none; font-weight: 600; }
        .container { 
            display: grid; grid-template-columns: 1fr 1fr; gap: 40px; 
            background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .img-box img { width: 100%; border-radius: 15px; }
        .price { font-size: 2rem; font-weight: 800; color: var(--primary); margin: 20px 0; }
        .description { line-height: 1.6; color: #64748b; margin-bottom: 30px; }
        .btn-buy { background: var(--accent); color: white; padding: 15px 40px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-block; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<a href="shop.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Shop</a>

<div class="container">
    <div class="img-box">
        <img src="uploads/<?php echo !empty($product['image']) ? $product['image'] : 'default.jpg'; ?>">
    </div>
    <div class="info">
        <h1 style="margin:0;"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
        <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <a href="checkout.php?id=<?php echo $product['id']; ?>" class="btn-buy">Buy Now</a>
    </div>
</div>

</body>
</html>