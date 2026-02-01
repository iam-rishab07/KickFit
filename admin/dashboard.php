<?php
session_start();
include "../db.php";

/* ---------- ACCESS CONTROL ---------- */
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if($_SESSION['user_role'] != "admin") {
    header("Location: ../dashboard.php");
    exit();
}

/* ---------- FETCH ADMIN DATA ---------- */
$admin_id = $_SESSION['user_id'];
$sql_admin = "SELECT name, email, phone, address, role FROM users WHERE id = '$admin_id'";
$result_admin = mysqli_query($conn, $sql_admin);
$admin = mysqli_fetch_assoc($result_admin);

/* ---------- DASHBOARD STATS ---------- */

// Total Products
$product_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];

// Total Users
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];

// Total Orders
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM single_order"))['total'];

// Total Revenue
$revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM single_order"))['total'];
if(!$revenue) $revenue = 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: Arial, Helvetica, sans-serif;
}

/* SIDEBAR */
.dashboard_sidebar{
    position: fixed;
    top:0;
    left:0;
    width:230px;
    height:100vh;
    background:#c0392b;
    padding-top:25px;
    box-shadow:2px 0 12px rgba(0,0,0,0.15);
}

.dashboard_sidebar h2{
    color:white;
    text-align:center;
    margin-bottom:25px;
    letter-spacing:1px;
}

.dashboard_sidebar ul{
    list-style:none;
}

.dashboard_sidebar ul li a{
    display:block;
    padding:13px 25px;
    color:white;
    text-decoration:none;
    transition:0.3s;
    font-weight:500;
}

.dashboard_sidebar ul li a:hover{
    background:#922b21;
    padding-left:32px;
}

/* MAIN AREA */
.dashboard_main{
    margin-left:230px;
    padding:35px 45px;
    background:#f4f6f9;
    min-height:100vh;
}

.dashboard_main h1{
    color:#c0392b;
    margin-bottom:25px;
}

/* ADMIN CARD */
.profile_card{
    background:white;
    padding:25px 30px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    max-width:600px;
}

.profile_card h2{
    color:#c0392b;
    margin-bottom:15px;
}

.profile_item{
    margin:10px 0;
    font-size:15px;
}

.profile_item span{
    font-weight:bold;
    color:#333;
}

/* RESPONSIVE */
@media(max-width:768px){
    .dashboard_sidebar{
        width:180px;
    }
    .dashboard_main{
        margin-left:180px;
        padding:25px;
    }
}

/* STATS GRID */
.stats_grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap:20px;
    margin-bottom:35px;
}

.stat_card{
    background:white;
    padding:22px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
    transition:0.3s;
}

.stat_card:hover{
    transform:translateY(-5px);
}

.stat_card h3{
    color:#888;
    font-size:14px;
    margin-bottom:10px;
}

.stat_card .number{
    font-size:28px;
    font-weight:bold;
    color:#c0392b;
}

</style>
</head>

<body>

<div class="dashboard_sidebar">
    <h2>KICKFIT ADMIN</h2>
    <ul>
        <li><a href="addproduct.php">Add Product</a></li>
        <li><a href="displayproduct.php">View Products</a></li>
        <li><a href="vieworders.php">View Orders</a></li>
        <li><a href="../index.php">Home</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard_main">
    <h1>Admin Dashboard</h1>
    <div class="stats_grid">

    <div class="stat_card">
        <h3>Total Products</h3>
        <div class="number"><?php echo $product_count; ?></div>
    </div>

    <div class="stat_card">
        <h3>Total Users</h3>
        <div class="number"><?php echo $user_count; ?></div>
    </div>

    <div class="stat_card">
        <h3>Total Orders</h3>
        <div class="number"><?php echo $order_count; ?></div>
    </div>

    <div class="stat_card">
        <h3>Total Revenue</h3>
        <div class="number">₹<?php echo $revenue; ?></div>
    </div>

</div>


    <div class="profile_card">
        <h2>Admin Profile</h2>

        <div class="profile_item"><span>Name:</span> <?php echo $admin['name']; ?></div>
        <div class="profile_item"><span>Email:</span> <?php echo $admin['email']; ?></div>
        <div class="profile_item"><span>Phone:</span> <?php echo $admin['phone']; ?></div>
        <div class="profile_item"><span>Address:</span> <?php echo $admin['address']; ?></div>
        <div class="profile_item"><span>Role:</span> <?php echo strtoupper($admin['role']); ?></div>
    </div>

</div>

</body>
</html>
