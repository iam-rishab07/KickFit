<?php
include "db.php";
$successMessage = '';
$errorMessage = '';

if(isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); // TODO: Hash password in production: password_hash($password, PASSWORD_DEFAULT)
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $role = "user";

    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email='$email'";
    $check_result = mysqli_query($conn, $check_email);
    if(mysqli_num_rows($check_result) > 0) {
        $errorMessage = "Email already registered!";
    } else {
        $sql = "INSERT INTO users(name, email, password, phone, address, role) VALUES ('$name', '$email', '$password', '$phone', '$address', '$role')";
        $result = mysqli_query($conn, $sql);
        if(!$result) {
            $errorMessage = "Error: " . mysqli_error($conn);
        } else {
            $successMessage = "Register Successfully! You can now login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* REGISTER CARD */
        .registerdiv {
            background: #ffffff;
            padding: 40px 35px;
            width: 360px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        /* TITLE */
        .registerdiv h2 {
            margin-bottom: 15px;
            color: #e74c3c;
            font-size: 24px;
        }

        /* MESSAGES */
        .message {
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* INPUTS */
        .registerdiv input,
        .registerdiv textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: 0.3s;
            resize: none;
            box-sizing: border-box;
        }

        .registerdiv input:focus,
        .registerdiv textarea:focus {
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
        .registerdiv a {
            display: block;
            margin-top: 12px;
            font-size: 14px;
            color: #555;
            text-decoration: none;
        }

        .registerdiv a:hover {
            color: #e74c3c;
        }

        /* BACK TO SHOP BUTTON */
        .shoplink {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #e74c3c;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .shoplink:hover {
            background: #333;
        }
    </style>
</head>
<body>
    <a class="shoplink" href="index.php">Shop</a>
    <div class="registerdiv">
        <?php if($successMessage): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
            <p><a href="login.php">Click here to login</a></p>
        <?php endif; ?>
        
        <?php if($errorMessage): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <h2>Register</h2>
        <form action="register.php" method="post">
            <input type="text" name="name" placeholder="Enter your Name here!" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            <input type="email" name="email" placeholder="Enter your Email here!" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <input type="password" name="password" placeholder="Enter your Password" required>
            <input type="text" name="phone" placeholder="Enter your Phone Number here!" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            <textarea name="address" placeholder="Enter your Address here!" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            <input class="button" type="submit" name="submit" value="Sign Up">
            <p>Already a user? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>
</html>
