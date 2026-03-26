<?php
include "db.php";
session_start();

$errorMessage = '';

if(isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)) {
        $errorMessage = "All fields are required!";
    } 
    else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if(password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_role'] = $row['role'];

                if($row['role'] === "admin") {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $errorMessage = "Invalid Email or Password!";
            }
        } else {
            $errorMessage = "Invalid Email or Password!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KickFit Elite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap');

        :root {
            --primary: #c0392b;
            --primary-hover: #a93226;
            --dark: #1a1c1e;
            --bg: #f8fafc;
            --glass: rgba(255, 255, 255, 0.9);
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image: radial-gradient(var(--primary) 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            background-opacity: 0.05;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark);
        }

        .login-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .brand-logo img {
            height: 50px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .login-card h2 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .login-card p.subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .error-toast {
            background: #fef2f2;
            color: #dc2626;
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            border: 1px solid #fee2e2;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group i.left-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: 0.3s;
            pointer-events: none;
        }

        .login-card input {
            width: 100%;
            /* Extra padding on the left for the icon, extra on the right for the toggle */
            padding: 16px 45px 16px 48px; 
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: #fff;
        }

        .login-card input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.1);
        }

        .login-card input:focus + i.left-icon {
            color: var(--primary);
        }

        /* FIXED TOGGLE POSITIONING */
        .pass-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            padding: 5px;
            transition: 0.2s;
            z-index: 10;
        }

        .pass-toggle:hover {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 14px;
            background: var(--dark);
            color: white;
            font-size: 16px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-login:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(192, 57, 43, 0.3);
        }

        .footer-links {
            margin-top: 30px;
            font-size: 14px;
            color: #64748b;
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="login-card">
    <div class="brand-logo">
        <a href="index.php"><img src="image/logoo.png" alt="KickFit Logo"></a>
    </div>

    <h2>Welcome Back</h2>
    <p class="subtitle">Securely log in to your KickFit account</p>

    <?php if($errorMessage): ?>
        <div class="error-toast">
            <i class="fas fa-circle-exclamation"></i>
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div class="input-group">
            <i class="fas fa-envelope left-icon"></i>
            <input type="email" name="email" placeholder="Email Address" required
            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="input-group">
            <i class="fas fa-lock left-icon"></i>
            <input type="password" name="password" id="passInput" placeholder="Password" required>
            <i class="fas fa-eye pass-toggle" id="toggleIcon" onclick="togglePass()"></i>
        </div>

        <button type="submit" name="submit" class="btn-login">
            Sign In <i class="fas fa-arrow-right"></i>
        </button>

        <div class="footer-links">
            New to KickFit? <a href="register.php">Create Account</a>
        </div>
    </form>
</div>

<script>
    function togglePass() {
        const input = document.getElementById('passInput');
        const icon = document.getElementById('toggleIcon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

</body>
</html>