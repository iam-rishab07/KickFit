<?php
include "../db.php";
session_start();
if(isset($_SESSION['user_id']))
    {
        if($_SESSION['user_role'] == "admin")
            {
                $sql1 = "select * from categories";
                $result1 = mysqli_query($conn,$sql1);
                if(isset($_POST['submit'])){
                    $name = $_POST['name'];
                    $description = $_POST['description'];
                    $price = $_POST['price'];
                    $stock = $_POST['stock'];
                    $image = $_FILES['image']['name'];
                    $temp_location = $_FILES['image']['tmp_name'];
                    $upload_location = "../image/";
                    $category_name = $_POST['category_name'];
                    $sql = "insert into products(name,description,price,stock,image,category_name) values('$name','$description','$price','$stock','$image','$category_name')";

                    $result = mysqli_query($conn,$sql);
                    if(!$result)
                        {
                            echo "Error : {$conn->error}";
                        }
                        else{
                            echo "Product Added Successfully!";
                            move_uploaded_file($temp_location,$upload_location.$image);
                        }
                }
            }else{
                echo "Go for user DashBoard";
            }
    }else{
        header("Location: ../index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard</title>
    <!-- <style>
        *{
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .dashboard_sidebar{
            position: fixed;
            top: 0;
            background-color: darkcyan;
            width: 200px;
            height: 100%;
        }
        .dashboard_sidebar ul li{
            list-style: none;
            text-align: center;
            
        }
        .dashboard_sidebar ul li a{
            padding: 10px;
            display: block;
            text-decoration: none;
            color: white;
        }
        .dashboard_sidebar ul li a:hover{
            background-color: black;
        }
        .dashboard_main{
            position: relative;
            padding: 30px;
            left: 45%;
            margin-top: 10px;
        }
        .dashboard_main input{
            display: block;
            margin: 10px;
            padding: 20px;
            border-left: 2px solid lightblue;
            border-right: 2px solid lightcoral;
            border-radius: 15px 50px;
        }
        .dashboard_main select{
            display: inline-block;
            margin: 10px;
            padding: 20px;
            border-left: 2px solid lightblue;
            border-right: 2px solid lightcoral;
            border-radius: 15px 50px;
        }
        .dashboard_main textarea{
            display: block;
            margin: 10px;
            padding: 20px;
            width: 30%;
            border-radius: 15px 50px;
        }
        .button{
            width: 15%;
            background-color: green;
            border-radius: 15px 50px;
            border-left: 2px solid lightblue;
            border-right: 2px solid lightcoral;
        }
    </style> -->
    <style>
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

/* SIDEBAR */
.dashboard_sidebar{
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    background: #c0392b;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    padding-top: 20px;
}

.dashboard_sidebar ul li{
    list-style: none;
}

.dashboard_sidebar ul li a{
    padding: 12px 20px;
    display: block;
    text-decoration: none;
    color: #ffffff;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.dashboard_sidebar ul li a:hover{
    background: #922b21;
    border-left: 4px solid #ffffff;
    padding-left: 26px;
}

/* MAIN AREA */
.dashboard_main{
    margin-left: 220px;
    min-height: 100vh;
    background: #f4f6f9;

    /* CENTER CONTENT */
    display: flex;
    justify-content: center;   /* horizontal center */
    align-items: center;       /* vertical center */
    padding: 40px 20px;
}

/* FORM CARD */
.dashboard_main form{
    background: #ffffff;
    width: 100%;
    max-width: 600px;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* INPUTS */
.dashboard_main input,
.dashboard_main select,
.dashboard_main textarea{
    width: 100%;
    margin-bottom: 15px;
    padding: 12px 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: 0.3s;
}

.dashboard_main input:focus,
.dashboard_main select:focus,
.dashboard_main textarea:focus{
    border-color: #c0392b;
    outline: none;
    box-shadow: 0 0 6px rgba(192,57,43,0.3);
}

.dashboard_main textarea{
    resize: vertical;
    min-height: 100px;
}

/* BUTTON */
.button{
    width: 100%;
    padding: 12px;
    background: #c0392b;
    border: none;
    border-radius: 6px;
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.button:hover{
    background: #922b21;
}
</style>


</head>
<body>
    <div class="dashboard_sidebar">
        <ul>
            <li><a href="addproduct.php">Add Product</a></li>
            <li><a href="displayproduct.php">View Products</a></li>
            <li><a href="vieworders.php">View Orders</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard_main">
        
            <form action="addproduct.php" method="post" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Enter Product Name!" required>
                <textarea name="description" placeholder="Enter Product Description" required></textarea>
                <input type="number" name="price" placeholder="Enter Price!" required>
                <input type="number" name="stock" placeholder="enter Stock number!" required>
                <h2>upload Image Here!</h2>
                <input type="file" name="image" required>
                <select name="category_name" >
                    <?php 
                while($row = mysqli_fetch_assoc($result1)){
                ?>
                    <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
                    <?php } ?>
                </select>
                
                <input class="button" type="submit" name="submit" value="Add Product">
            </form>
        
    </div>
</body>
</html>