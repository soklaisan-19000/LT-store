<?php
session_start();
include 'db.php'; 

if (isset($_POST['login'])) {
    $user_input = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password']; 

    // Note: It is highly recommended to use password_hash() and password_verify() 
    // instead of plain text passwords for real projects.
    $query = "SELECT * FROM users WHERE (email='$user_input' OR username='$user_input') AND password='$pass'";
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
        $error = "Invalid Username/Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LT-STORE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --success: #27ae60;
            --bg: #f4f7f6;
        }

        /* --- Animations --- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, var(--primary) 50%, var(--bg) 50%);
            display: flex; 
            justify-content: center;
            align-items: center; 
            height: 100vh;
            margin: 0; 
            overflow: hidden;
        }

        .login-card { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
            width: 100%;
            max-width: 380px; 
            text-align: center;
            animation: fadeInUp 0.6s ease-out; /* Apply Animation */
        }

        .brand-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        
        .brand-icon:hover { transform: scale(1.1) rotate(10deg); }

        h2 { color: var(--primary); margin: 0 0 10px 0; font-size: 1.8rem; }
        p.subtitle { color: #7f8c8d; margin-bottom: 30px; font-size: 0.9rem; }

        .form-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group i.input-icon {
            position: absolute;
            left: 15px;
            top: 15px;
            color: #bdc3c7;
        }

        /* Toggle Password Style */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 15px;
            color: #bdc3c7;
            cursor: pointer;
            transition: 0.3s;
        }

        .toggle-password:hover { color: var(--accent); }

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
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        button:hover { background: var(--accent); transform: translateY(-2px); }
        button:active { transform: translateY(0); }

        .error-box {
            background: #fdf2f2;
            color: #ec4899;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            border: 1px solid #f9a8d4;
            animation: fadeInUp 0.3s ease;
        }

        .divider { margin: 25px 0; border-bottom: 1px solid #eee; position: relative; }
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
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="email" placeholder="Username or Email" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" id="passwordField" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" id="toggleEye"></i>
            </div>
            <button type="submit" name="login" id="submitBtn">
                <span id="btnText">Sign In</span>
            </button>
        </form>

        <div class="divider"><span>OR</span></div>

        <a href="shop.php" class="guest-link">Browse Shop as Guest <i class="fas fa-arrow-right"></i></a>
    </div>

    <script>
        // 1. Toggle Password Visibility
        const toggleEye = document.querySelector('#toggleEye');
        const passwordField = document.querySelector('#passwordField');

        toggleEye.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle the eye icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // 2. Loading Animation on Submit
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');

        loginForm.addEventListener('submit', function() {
            // Disable button and show loading state
            submitBtn.style.opacity = "0.7";
            submitBtn.style.pointerEvents = "none";
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
        });
    </script>
</body>
</html>