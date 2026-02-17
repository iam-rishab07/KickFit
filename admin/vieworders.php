<?php
session_start();
include "../db.php";


if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "admin"){
    header("Location: ../index.php");
    exit();
}



/* 1️ DIRECT BUY NOW ORDERS */
$sql_single = "SELECT 
    so.id AS order_id,
    u.name AS user_name,
    u.email AS user_email,
    pr.name AS product_name,
    pr.image AS product_image,
    so.product_quantity AS quantity,
    p.total_amount,
    p.payment_method,
    'Buy Now' AS order_type
FROM single_order so
JOIN users u ON so.user_id = u.id
JOIN products pr ON so.product_id = pr.id
JOIN payments p ON p.order_id = so.id";

/* 2️ CART CHECKOUT ORDERS */
$sql_cart = "SELECT 
    o.id AS order_id,
    u.name AS user_name,
    u.email AS user_email,
    pr.name AS product_name,
    pr.image AS product_image,
    oi.quantity,
    p.total_amount,
    p.payment_method,
    'Cart Order' AS order_type
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN users u ON o.user_id = u.id
JOIN products pr ON oi.product_id = pr.id
JOIN payments p ON p.order_id = o.id";

/* COMBINE BOTH */
$sql = "($sql_single) UNION ALL ($sql_cart) ORDER BY order_id DESC";

$result = mysqli_query($conn,$sql);
if(!$result){
    die("Error fetching orders: ".$conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Orders - Admin</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}

/* SIDEBAR */
.dashboard_sidebar{
position:fixed;top:0;left:0;width:220px;height:100vh;
background:#c0392b;color:white;padding-top:20px;
box-shadow:2px 0 10px rgba(0,0,0,0.1);
}
.dashboard_sidebar ul li{list-style:none}
.dashboard_sidebar ul li a{
display:block;color:white;text-decoration:none;
padding:12px 20px;transition:.3s;border-left:4px solid transparent;
}
.dashboard_sidebar ul li a:hover{
background:#922b21;border-left:4px solid #fff;
}

/* MAIN */
.dashboard_main{
margin-left:220px;padding:30px;background:#f4f6f9;min-height:100vh;
}
h1{color:#c0392b;margin-bottom:20px;text-align:center}

/* TABLE */
table{
width:100%;border-collapse:collapse;background:white;
border-radius:8px;overflow:hidden;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
}
th{
background:#c0392b;color:white;padding:14px;text-align:center;font-size:14px;
}
td{
padding:12px;text-align:center;border-bottom:1px solid #eee;
}
tr:nth-child(even){background:#f9f9f9}
tr:hover{background:#fdecea}
td img{
width:70px;height:70px;object-fit:cover;border-radius:6px
}

/* TYPE BADGE */
.type{
padding:4px 8px;border-radius:5px;font-size:12px;color:white;
}
.buy{background:#27ae60}
.cart{background:#2980b9}
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
                <th>User</th>
                <th>Email</th>
                <th>Product</th>
                <th>Image</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Type</th>
                </tr>
            </thead>

            <tbody>
                <?php while($row=mysqli_fetch_assoc($result)){ ?>
                <tr>
                    <td>#<?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['user_name']; ?></td>
                    <td><?php echo $row['user_email']; ?></td>
                    <td><?php echo $row['product_name']; ?></td>
                    <td><img src="../image/<?php echo $row['product_image']; ?>"></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>₹ <?php echo $row['total_amount']; ?></td>
                    <td><?php echo ucfirst($row['payment_method']); ?></td>
                    <td>
                    <?php if($row['order_type']=="Buy Now"){ ?>
                    <span class="type buy">Buy Now</span>
                    <?php } else { ?>
                    <span class="type cart">Cart</span>
                    <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</body>
</html>
