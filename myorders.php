<?php
include "db.php";
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if($_SESSION['user_role'] != "user"){
    header("Location: admin/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
        p.order_id,
        pr.name AS product_name,
        pr.image,
        so.product_quantity,
        p.total_amount,
        p.payment_method
        FROM payments p
        JOIN single_order so ON p.order_id = so.id
        JOIN products pr ON so.product_id = pr.id
        WHERE p.user_id = '$user_id'
        ORDER BY p.id DESC";

$result = mysqli_query($conn,$sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>My Orders</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}

.dashboard_sidebar{
position:fixed;top:0;left:0;width:220px;height:100vh;
background:#c0392b;color:white;padding-top:20px
}
.dashboard_sidebar a{
display:block;color:white;text-decoration:none;
padding:12px 20px;transition:0.3s
}
.dashboard_sidebar a:hover{background:#922b21}

.dashboard_main{
margin-left:220px;padding:30px;background:#f4f6f9;min-height:100vh
}

h1{color:#c0392b;margin-bottom:20px}

table{
width:100%;border-collapse:collapse;background:white;
border-radius:8px;overflow:hidden;
box-shadow:0 4px 12px rgba(0,0,0,0.08)
}

th{
background:#c0392b;color:white;padding:14px;text-align:center
}
td{
padding:12px;text-align:center;border-bottom:1px solid #eee
}

tr:nth-child(even){background:#f9f9f9}
tr:hover{background:#fdecea}

td img{
width:70px;height:70px;object-fit:cover;border-radius:6px
}

.status{
background:#27ae60;color:white;padding:4px 8px;border-radius:5px;font-size:12px
}
</style>
</head>
<body>

<div class="dashboard_sidebar">
<a href="dashboard.php">Dashboard</a>
<a href="index.php">Shop</a>
<a href="logout.php">Logout</a>
</div>

<div class="dashboard_main">
<h1>My Orders</h1>

<table>
<tr>
<th>Order ID</th>
<th>Product</th>
<th>Image</th>
<th>Qty</th>
<th>Total</th>
<th>Payment</th>
<th>Status</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>
<tr>
<td>#<?php echo $row['order_id']?></td>
<td><?php echo $row['product_name']?></td>
<td><img src="image/<?php echo $row['image']?>"></td>
<td><?php echo $row['product_quantity']?></td>
<td>Rs. <?php echo $row['total_amount']?></td>
<td><?php echo $row['payment_method']?></td>
<td><span class="status">Confirmed</span></td>
</tr>
<?php } ?>

</table>
</div>
</body>
</html>
