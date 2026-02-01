<?php
session_start();
include "db.php";

/* ---------- ACCESS CONTROL ---------- */
if(isset($_SESSION['user_id'])){
    if($_SESSION['user_role'] != "user"){
        header("Location: admin/dashboard.php");
        exit();
    }
}else{
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------- FETCH USER INFO ---------- */
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));

/* ---------- USER STATS ---------- */

// Total Orders
$order_count = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM single_order WHERE user_id='$user_id'"
))['total'];

// Total Money Spent
$total_spent = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(total_amount) as total FROM single_order WHERE user_id='$user_id'"
))['total'];

if(!$total_spent) $total_spent = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial, Helvetica, sans-serif;}

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

.dashboard_sidebar ul li{list-style:none;}

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

.dashboard_main{
    margin-left: 220px;
    padding: 30px 40px;
    background: #f4f6f9;
    min-height: 100vh;
}

.dashboard_main h1{
    color:#c0392b;
    margin-bottom:20px;
}

/* STATS */
.stats_grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap:20px;
    margin-bottom:30px;
}

.stat_card{
    background:white;
    padding:22px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.stat_card h3{
    color:#888;
    font-size:14px;
    margin-bottom:10px;
}

.stat_card .number{
    font-size:26px;
    font-weight:bold;
    color:#c0392b;
}

/* PROFILE */
.profile_card{
    background:#ffffff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,0.06);
}

.profile_card p{
    margin-bottom:8px;
}
</style>
</head>

<body>

<div class="dashboard_sidebar">
    <ul>
        <li><a href="myorders.php">My Orders</a></li>
        <li><a href="index.php">Home</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard_main">

    <h1>Welcome, <?php echo $user['name']; ?> 👋</h1>

    <!-- STATS -->
    <div class="stats_grid">
        <div class="stat_card">
            <h3>Total Orders</h3>
            <div class="number"><?php echo $order_count; ?></div>
        </div>

        <div class="stat_card">
            <h3>Total Spent</h3>
            <div class="number">₹<?php echo $total_spent; ?></div>
        </div>
    </div>

    <!-- PROFILE INFO -->
    <div class="profile_card">
        <h2>Your Details</h2>
        <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        <p><strong>Phone:</strong> <?php echo $user['phone']; ?></p>
        <p><strong>Address:</strong> <?php echo $user['address']; ?></p>
    </div>

</div>

</body>
</html>
