<?php
session_start();
include 'db.php';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: shop.php");
        }
    } else {
        $error = "Invalid Email or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SMS System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --bg: #f4f7f6;
            --text: #34495e;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
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
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-card h2 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .login-card p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 40px;
            color: #bdc3c7;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: bold;
            color: var(--text);
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.2);
        }

        .btn-login {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div style="font-size: 3rem; color: var(--accent); margin-bottom: 10px;">
        <i class="fas fa-user-shield"></i>
    </div>
    <h2>Welcome Back</h2>
    <p>Please login to your account</p>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="email@example.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" name="login" class="btn-login">
            Login <i class="fas fa-sign-in-alt" style="margin-left: 8px;"></i>
        </button>
    </form>
    
    <div style="margin-top: 25px; font-size: 0.8rem; color: #95a5a6;">
        Secure Management System &copy; <?php echo date('Y'); ?>
    </div>
</div>

</body>
</html>