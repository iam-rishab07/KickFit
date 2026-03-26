<?php
include "db.php";

$successMessage = '';
$errorMessage = '';

if(isset($_POST['submit'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $role     = "user";

    // Validation
    if(empty($name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        $errorMessage = "All fields are required!";
    }
    // PHONE VALIDATION: Check if it's exactly 10 digits
    elseif(!preg_match('/^[0-9]{10}$/', $phone)) {
        $errorMessage = "Phone number must be exactly 10 digits!";
    }
    elseif(strlen($password) < 8) {
        $errorMessage = "Password must be at least 8 characters long!";
    }
    elseif(!preg_match('/[0-9]/', $password)) {
        $errorMessage = "Password must contain at least one number!";
    }
    elseif(!preg_match('/[\W]/', $password)) {
        $errorMessage = "Password must contain at least one special character!";
    }
    else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if($checkStmt->num_rows > 0) {
            $errorMessage = "Already a user! Please login.";
        } 
        else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $phone, $address, $role);

            if($stmt->execute()) {
                $successMessage = "Registered Successfully! You can now login.";
            } else {
                $errorMessage = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Fam | KickFit Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --dark: #1a1c1e;
            --bg: #f8fafc;
        }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--bg);
            background-image: radial-gradient(#c0392b10 1px, transparent 1px);
            background-size: 30px 30px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .reg-card {
            background: #ffffff;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            text-align: center;
        }

        .brand-logo img { height: 40px; margin-bottom: 20px; }

        .reg-card h2 { color: var(--dark); font-size: 24px; font-weight: 800; margin-bottom: 8px; }
        .reg-card p.subtitle { color: #888; font-size: 14px; margin-bottom: 25px; }

        .msg { padding: 12px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid; display: flex; align-items: center; gap: 10px; }
        .msg-success { background: #f6ffed; color: #52c41a; border-color: #b7eb8f; }
        .msg-error { background: #fff1f0; color: #d83d31; border-color: #ffa39e; }

        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        .input-group i { position: absolute; left: 15px; top: 15px; color: #aaa; transition: 0.3s; z-index: 10; }
        
        .reg-card input, .reg-card textarea {
            width: 100%;
            padding: 13px 13px 13px 45px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            font-size: 14px;
            transition: 0.3s;
            box-sizing: border-box;
            background: #fafafa;
            resize: none;
        }

        .reg-card input:focus, .reg-card textarea:focus {
            border-color: var(--primary);
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.1);
        }

        .btn-reg {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: var(--dark);
            color: white;
            font-size: 15px;
            cursor: pointer;
            font-weight: 700;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-reg:hover { background: var(--primary); transform: translateY(-2px); }

        .footer-links { margin-top: 20px; font-size: 14px; color: #666; }
        .footer-links a { color: var(--primary); text-decoration: none; font-weight: 700; }

        .shop-nav { position: fixed; top: 20px; left: 20px; text-decoration: none; color: var(--dark); font-weight: 700; font-size: 14px; background: #fff; padding: 10px 20px; border-radius: 50px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .shop-nav:hover { background: var(--primary); color: #fff; }
    </style>
</head>
<body>

<a class="shop-nav" href="index.php"><i class="fas fa-shopping-bag"></i> Shop</a>

<div class="reg-card">
    <div class="brand-logo">
        <img src="image/logoo.png" alt="KickFit">
    </div>

    <h2>Create Account</h2>
    <p class="subtitle">Join the elite sneaker community today.</p>

    <?php if($successMessage): ?>
        <div class="msg msg-success"><i class="fas fa-circle-check"></i> <?php echo $successMessage; ?></div>
        <p><a href="login.php" style="color:var(--primary); font-weight:800; text-decoration:none;">Go to Login &rarr;</a></p>
    <?php endif; ?>

    <?php if($errorMessage): ?>
        <div class="msg msg-error"><i class="fas fa-circle-exclamation"></i> <?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <form action="register.php" method="post" <?php if($successMessage) echo 'style="display:none;"'; ?>>
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="name" placeholder="Full Name" required 
                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email Address" required 
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Create Password" required>
        </div>

        <div class="input-group">
            <i class="fas fa-phone"></i>
            <input type="tel" name="phone" placeholder="Phone Number (10 digits)" 
                pattern="[0-9]{10}" maxlength="10" required 
                title="Please enter a valid 10-digit phone number"
                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        </div>

        <div class="input-group">
            <i class="fas fa-map-marker-alt" style="top: 20px;"></i>
            <textarea name="address" placeholder="Delivery Address" rows="3" required><?php 
                echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; 
            ?></textarea>
        </div>

        <button type="submit" name="submit" class="btn-reg">Sign Up</button>

        <div class="footer-links">
            Already a member? <a href="login.php">Login Here</a>
        </div>
    </form>
</div>

</body>
</html>