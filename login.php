<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LT SMS STORE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e293b;
            --accent: #3b82f6;
            --bg: #f8fafc;
            --text: #334155;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, #0f172a 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-card h2 {
            color: var(--primary);
            margin: 15px 0 5px;
            font-size: 1.8rem;
            font-weight: 800;
        }

        .login-card p {
            color: #64748b;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 42px;
            color: #94a3b8;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            padding-left: 5px;
        }

        input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
            background: #f8fafc;
            font-size: 1rem;
        }

        input:focus {
            border-color: var(--accent);
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-login {
            background: var(--accent);
            color: white;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .error-msg {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #fee2e2;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div style="font-size: 3.5rem; color: var(--accent);">
        <i class="fas fa-shield-halved"></i>
    </div>
    <h2>LT STORE</h2>
    <p>Sign in with Username or Email</p>

    <?php if (isset($error)): ?>
        <div class="error-msg">
            <i class="fas fa-circle-exclamation" style="margin-right: 5px;"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username or Email</label>
            <i class="fas fa-user"></i>
            <input type="text" name="user_input" placeholder="Enter username or email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" name="login" class="btn-login">
            Sign In <i class="fas fa-arrow-right"></i>
        </button>
    </form>
    
    <div style="margin-top: 30px; font-size: 0.8rem; color: #94a3b8;">
        LT SMS MANAGEMENT &copy; <?php echo date('Y'); ?>
    </div>
</div>

</body>
</html>