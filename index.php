<?php
session_start();
// Database connection
include 'db.php'; // Using your include file for consistency

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password']; 

    $query = "SELECT * FROM users WHERE email='$email' AND password='$pass'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: shop.php");
        }
        exit();
    } else {
        $error = "Invalid Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ecom Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --success: #27ae60;
            --bg: #f4f7f6;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, var(--primary) 50%, var(--bg) 50%);
            display: flex; 
            justify-content: center;
            align-items: center; 
            height: 100vh;
            margin: 0; 
        }

        .login-card { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
            width: 100%;
            max-width: 380px; 
            text-align: center; 
        }

        .brand-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 15px;
        }

        h2 { color: var(--primary); margin: 0 0 10px 0; font-size: 1.8rem; }
        p.subtitle { color: #7f8c8d; margin-bottom: 30px; font-size: 0.9rem; }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 15px;
            color: #bdc3c7;
        }

        input { 
            width: 100%; 
            padding: 12px 15px 12px 45px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }

        input:focus { border-color: var(--accent); box-shadow: 0 0 8px rgba(52,152,219,0.2); }

        button { 
            width: 100%; 
            padding: 13px; 
            background: var(--primary); 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold; 
            transition: 0.3s;
        }

        button:hover { background: var(--accent); transform: translateY(-2px); }

        .error-box {
            background: #fdf2f2;
            color: #ec4899;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            border: 1px solid #f9a8d4;
        }

        .divider { 
            margin: 25px 0; 
            border-bottom: 1px solid #eee; 
            position: relative;
        }
        
        .divider span {
            position: absolute;
            top: -10px;
            background: white;
            padding: 0 10px;
            left: 50%;
            transform: translateX(-50%);
            color: #95a5a6;
            font-size: 0.8rem;
        }

        .guest-link { 
            display: inline-block; 
            color: var(--accent); 
            text-decoration: none; 
            font-weight: 600;
            font-size: 14px; 
            transition: 0.2s;
        }

        .guest-link:hover { color: var(--primary); text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-icon"><i class="fas fa-shopping-bag"></i></div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Enter your credentials to access your account</p>
        
        <?php if(isset($error)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login">Sign In</button>
        </form>

        <div class="divider"><span>OR</span></div>

        <a href="shop.php" class="guest-link">Browse Shop as Guest <i class="fas fa-arrow-right"></i></a>
    </div>
</body>
</html>