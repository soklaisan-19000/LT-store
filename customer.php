<?php
session_start();
if ($_SESSION['role'] != 'customer') { header("Location: index.php"); exit(); }
$conn = mysqli_connect("localhost", "root", "", "ecommerce_db");
$products = mysqli_query($conn, "SELECT * FROM products");
?>
<!DOCTYPE html>
<html>
<head><title>Customer Shop</title></head>
<body>
    <h1>Welcome, <?php echo $_SESSION['user']; ?></h1>
    <a href="logout.php">Logout</a>
    <hr>
    <h3>Available Products</h3>
    <div style="display:flex; gap: 20px;">
        <?php while($row = mysqli_fetch_assoc($products)): ?>
            <div style="border:1px solid #ccc; padding:10px;">
                <h4><?php echo $row['name']; ?></h4>
                <p>Price: $<?php echo $row['price']; ?></p>
                <button>Buy Now</button>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>