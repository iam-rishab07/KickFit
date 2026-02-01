<?php
session_start();
include "../db.php";

// Check admin login
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "admin"){
    header("Location: ../index.php");
    exit();
}

// Fetch all orders with user and product info
$sql = "SELECT 
            so.id AS order_id,
            u.id AS user_id,
            u.name AS user_name,
            u.email AS user_email,
            pr.name AS product_name,
            pr.image AS product_image,
            so.product_quantity,
            p.total_amount,
            p.payment_method
        FROM single_order so
        JOIN users u ON so.user_id = u.id
        JOIN products pr ON so.product_id = pr.id
        JOIN payments p ON p.order_id = so.id
        ORDER BY so.id DESC";

$result = mysqli_query($conn,$sql);
if(!$result){
    die("Error fetching orders: ".$conn->error);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Orders - Admin</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial, Helvetica, sans-serif}

/* SIDEBAR */
.dashboard_sidebar{
position:fixed;top:0;left:0;width:220px;height:100vh;
background:#c0392b;color:white;padding-top:20px;
box-shadow:2px 0 10px rgba(0,0,0,0.1);
}
.dashboard_sidebar ul{padding-top:10px;}
.dashboard_sidebar ul li{list-style:none;margin-bottom:5px;}
.dashboard_sidebar ul li a{
display:block;color:white;text-decoration:none;padding:12px 20px;transition:0.3s;
border-left:4px solid transparent;
}
.dashboard_sidebar ul li a:hover{
background:#922b21;
border-left:4px solid #fff;
}

/* MAIN CONTENT */
.dashboard_main{
margin-left:220px;padding:30px 40px;background:#f4f6f9;min-height:100vh;
}
h1{color:#c0392b;margin-bottom:20px;text-align:center}

/* TABLE */
table{
width:100%;
border-collapse:collapse;
background:white;
border-radius:8px;
overflow:hidden;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
margin-top:20px;
}
th{
background-color:#c0392b;color:white;padding:14px;text-align:center;font-size:14px;text-transform:uppercase;
}
td{
padding:12px;font-size:14px;color:#333;border-bottom:1px solid #eee;text-align:center;
}
tr:nth-child(even){background:#f9f9f9}
tr:hover{background:#fdecea;transition:0.2s}
td img{
width:70px;height:70px;object-fit:cover;border-radius:6px;
}

/* BUTTONS (Optional for Actions) */
.update,.delete{
text-decoration:none;padding:6px 10px;border-radius:5px;font-size:13px;font-weight:bold;transition:0.3s;display:inline-block;
}
.update{background-color:#27ae60;color:white}
.update:hover{background-color:#1e8449}
.delete{background-color:#e74c3c;color:white}
.delete:hover{background-color:#922b21}
</style>
</head>
<body>

<div class="dashboard_sidebar">
<ul>
<li><a href="dashboard.php">Dashboard</a></li>
<li><a href="addproduct.php">Add Product</a></li>
<li><a href="displayproduct.php">View Products</a></li>
<li><a href="vieworders.php">View Orders</a></li>
<li><a href="../index.php">Home</a></li>
<li><a href="../logout.php">Logout</a></li>
</ul>
</div>

<div class="dashboard_main">
<h1>All Orders</h1>
<table>
<thead>
<tr>
<th>Order ID</th>
<th>User Name</th>
<th>Email</th>
<th>Product</th>
<th>Image</th>
<th>Quantity</th>
<th>Total Amount</th>
<th>Payment Method</th>
</tr>
</thead>
<tbody>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td>#<?php echo $row['order_id']; ?></td>
<td><?php echo $row['user_name']; ?></td>
<td><?php echo $row['user_email']; ?></td>
<td><?php echo $row['product_name']; ?></td>
<td><img src="../image/<?php echo $row['product_image']; ?>" alt="Product"></td>
<td><?php echo $row['product_quantity']; ?></td>
<td>Rs. <?php echo $row['total_amount']; ?></td>
<td><?php echo ucfirst($row['payment_method']); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

</body>
</html>
