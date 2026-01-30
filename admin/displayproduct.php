<?php
include "../db.php";
session_start();
if(isset($_SESSION['user_id']))
    {
        if($_SESSION['user_role'] == "admin")
            {
                $sql = "select * from products";
                $result = mysqli_query($conn,$sql);

                    if(!$result)
                        {
                            echo "Error : {$conn->error}";
                        }
                        else{
                            
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
    <title>display products</title>
 
      <!-- <style>
        table{
            width: 100%;
            border: none;
        }
        th{
            border-top: 2px solid darkblue;
        }
        tr, th, td{
            text-align: center;
            padding: 10px;
            border-bottom: 2px solid blue;
        }
        td{
            background-color: lightblue;
        }
        .update{
            background-color: lightgreen;
            text-decoration: none;
            padding: 1px;
        }
        .delete{
            background-color: lightcoral;
            text-decoration: none;
            padding: 10px;
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
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

/* TABLE */
table{
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-top: 20px;
}

/* HEADER */
th{
    background-color: #c0392b;
    color: white;
    padding: 14px 10px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;   /* ✅ CENTER HEADER */
}

/* CELLS */
td{
    padding: 12px 10px;
    font-size: 14px;
    color: #333;
    border-bottom: 1px solid #eee;
    text-align: center;   /* ✅ CENTER DATA */
}

/* ZEBRA STRIPES */
tr:nth-child(even){
    background-color: #f8f9fa;
}

/* HOVER */
tr:hover{
    background-color: #fdecea;
    transition: 0.2s ease;
}

/* BUTTONS */
.update,
.delete{
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: bold;
    transition: 0.3s;
    display: inline-block;
}

.update{
    background-color: #27ae60;
    color: white;
}
.update:hover{
    background-color: #1e8449;
}

.delete{
    background-color: #e74c3c;
    color: white;
}
.delete:hover{
    background-color: #922b21;
}
th, td {
    border-right: 1px solid #eee;
}

th:last-child,
td:last-child {
    border-right: none;
}

</style>

</head>
<body>
    <div class="dashboard_sidebar">
        <ul>
            <li><a href="addproduct.php">Add Product</a></li>
            <li><a href="displayproduct.php">View Order</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="dashboard_main">
        <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Image</th>
                <th>Category Name</th>
                <th>Action</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) {
                ?>
            <tr>
                <td><?php echo $row['name']?></td>
                <td><?php echo $row['description']?></td>
                <td><?php echo $row['price']?></td>
                <td><?php echo $row['stock']?></td>
                <td><img src="../image/<?php echo $row['image']?>" alt=""></td>
                <td><?php echo $row['category_name']?></td>
                <td><a class="update" href="#">Update</a></td>
                <td><a class="delete" href="deleteproduct.php?product_id=<?php echo $row['id']?>">Delete</a></td>
            </tr>
            <?php }
            ?>
        </tbody>
    </table>
    </div>
</body>
</html>