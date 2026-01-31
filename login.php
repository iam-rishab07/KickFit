<?php
    include "db.php";
    session_start();
    if(isset($_POST['submit']))
        {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $sql = "select * from users where email='$email'";
            $result = mysqli_query($conn,$sql);
            if($result->num_rows>0)
            {
                $row = mysqli_fetch_assoc($result);
                if($row['password'] == $password)
                    {
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['user_name'] = $row['name'];
                        $_SESSION['user_role'] = $row['role'];
                        if($_SESSION['user_role'] == "admin")
                            {
                                header("Location: admin/dashboard.php");
                            }else{
                                header("Location: index.php");
                                exit();
                            }
                    }
                    else{
                        echo "Wrong Password :(";
                    }
            }
            else{
                echo "please Sign Up!";
            }
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- <style>
        .login{
            position: fixed;
            top: 35%;
            left: 43%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: grey;
            padding: 30px;
        }
        .login input{
            border-bottom: 2px solid darkblue;
            display: block;
            padding: 10px;
            border-radius: 15px 50px;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .login a{
            color: lightblue;
            margin-left: 5px;
        }
        .button{
            border: 2px solid darkgray;
            background-color: darkcyan;
            width:100%;
        }
    </style> -->
    
<style>
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background-color: #f5f5f5;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* LOGIN CARD */
.login {
    background: #ffffff;
    padding: 40px 35px;
    width: 340px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.08);
    text-align: center;
}

/* TITLE */
.login h2 {
    margin-bottom: 20px;
    color: #e74c3c;
    font-size: 24px;
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
    display: block;
    margin-top: 12px;
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
        <form action="login.php" method="post">
            <input type="email" name="email" placeholder="Enter your Email here!" required>
            <input type="password" name="password" placeholder="Enter your Password" required>
            <input class="button" type="submit" name="submit" value="Login">
            <p>Did'nt Register Yet?<a href="register.php">Sign Up</a></p>
        </form>
    </div>
</body>
</html>