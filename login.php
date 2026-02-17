<?php
include "db.php";
session_start();
$successMessage = '';
$errorMessage = '';

if(isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if($row['password'] == $password) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_role'] = $row['role'];
            if($_SESSION['user_role'] == "admin") {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $errorMessage = "Wrong Password :(";
        }
    } else {
        $errorMessage = "Please Sign Up!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - KickFit</title>

<style>
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: linear-gradient(135deg,#f2f3f7,#ffffff);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* LOGIN CARD */
.login {
    background: #ffffff;
    padding: 40px 35px;
    width: 360px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    text-align: center;
    position: relative;
}

/* LOGO */
.logo img{
    width: 140px;
    margin-bottom: 15px;
}

/* TITLE */
.login h2 {
    margin-bottom: 15px;
    color: #e74c3c;
    font-size: 24px;
}

/* MESSAGES */
.message {
    padding: 12px;
    margin: 15px 0;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
}
.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* INPUT FIELDS */
.login input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: 0.3s;
    box-sizing: border-box;
}

.login input:focus {
    border-color: #e74c3c;
    outline: none;
    box-shadow: 0 0 5px rgba(231,76,60,0.3);
}

/* BUTTON */
.button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border: none;
    border-radius: 6px;
    background: #e74c3c;
    color: white;
    font-size: 15px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

.button:hover {
    background: #333;
}

/* LINKS */
.login a {
    font-size: 14px;
    color: #555;
    text-decoration: none;
}

.login a:hover {
    color: #e74c3c;
}
</style>
</head>

<body>

<div class="login">

    <!-- LOGO -->
    <div class="logo">
        <a href="index.php">
            <img src="image/logoo.png" alt="KickFit Logo">
        </a>
    </div>

    <?php if($errorMessage): ?>
        <div class="message error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <h2>Login</h2>

    <form action="login.php" method="post">
        <input type="email" name="email" placeholder="Enter your Email" required 
        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <input type="password" name="password" placeholder="Enter your Password" required>

        <input class="button" type="submit" name="submit" value="Login">

        <p style="margin-top:15px;">
            Didn't Register Yet? <a href="register.php">Sign Up</a>
        </p>
    </form>
</div>

</body>
</html>
