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
    <title>Document</title>
    <style>
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
        </form>
    </div>
</body>
</html>