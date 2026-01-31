<?php
session_start();
if(isset($_SESSION['user_id']))
    {
        if($_SESSION['user_role'] == "admin")
            {
                
            }else{
                header("Location: ../dashboard.php");
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
            padding: 30px;
            margin-left: 200px;
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
    background: #c0392b; /* deep red to match brand */
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    padding-top: 20px;
}

.dashboard_sidebar ul{
    padding-top: 10px;
}

.dashboard_sidebar ul li{
    list-style: none;
    text-align: left;
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

/* HOVER EFFECT */
.dashboard_sidebar ul li a:hover{
    background: #922b21;
    border-left: 4px solid #ffffff;
    padding-left: 26px;
}

/* MAIN CONTENT AREA */
.dashboard_main{
    margin-left: 220px;
    padding: 30px 40px;
    background: #f4f6f9; /* light grey professional bg */
    min-height: 100vh;
}

/* HEADINGS INSIDE MAIN */
.dashboard_main h1,
.dashboard_main h2{
    color: #c0392b;
    margin-bottom: 15px;
}

/* CONTENT BOX STYLE */
.dashboard_main p{
    background: #ffffff;
    padding: 18px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    line-height: 1.6;
}
</style>

</head>
<body>
    <div class="dashboard_sidebar">
        <ul>
            <li><a href="addproduct.php">Add Product</a></li>
            <li><a href="displayproduct.php">View Products</a></li>
            <li><a href="vieworders.php">View Orders</a></li>
            <li><a href="../index.php">Home</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard_main">
        <p>
            <h1>This is Admin-Dashboard</h1>
            <h2>Admin Details will Display Here</h2>
        </p>
    </div>
</body>
</html>