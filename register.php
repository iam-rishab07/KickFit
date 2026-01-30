<?php
include "db.php";
if(isset($_POST['submit']))
    {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $role = "user";

        $sql = "insert into users(name,email,password,phone,address,role) values ('$name','$email','$password','$phone','$address','$role')";
        $result = mysqli_query($conn,$sql);
        if(!$result)
            {
                echo "Error:{$conn->error}";
            }else{
                echo "Register Successfully!";
            }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <!-- <style>
        .registerdiv{
            margin-top: 200px;
            display: flex;
            flex-direction: row;
            justify-content: center;
            flex-wrap: wrap;
        }
        .shoplink{
            display: block;
            text-decoration: none;
            width: 100px;
            position:fixed;
            top: 20%;
            left: 46%;
            background-color: lightblue;
            padding: 10px;
            text-align: center;
        }
        .registerdiv input{
            display: block;
            padding: 15px;
            margin: 8px;
        }
        .registerdiv textarea{
            display: block;
            padding: 15px;
            margin: 8px;
            width: 162px;
        }
        .button{
            width: 200px;
            background-color: lightcoral;
            border: none;
        }
        .button:hover{
            background-color: greenyellow;
        }
        .registerdiv a{
            color: darkblue;
            margin-left: 5px;
        }
    </style> -->

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


        <form action="register.php" method="post">
            <input type="text" name="name" placeholder="Enter your Name here!" required>
            <input type="email" name="email" placeholder="Enter your Email here!" required>
            <input type="password" name="password" placeholder="Enter your Password" required>
            <input type="text" name="phone" placeholder="Enter your Phone Number here!" required>
            <textarea name="address" placeholder="Enter your Address here!" required></textarea>
            <input class="button" type="submit" name="submit" value="Sign Up">
            <p>Already a user?<a href="login.php">login</a></p>
        </form>
    </div>
</body>
</html>