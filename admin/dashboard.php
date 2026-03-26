<?php
session_start();
include "../db.php";

/* ---------- ACCESS CONTROL (Prepared) ---------- */
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, phone, address, role FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

/* ---------- DASHBOARD STATS ---------- */
$product_count = $conn->query("SELECT COUNT(*) as t FROM products")->fetch_assoc()['t'];
$user_count = $conn->query("SELECT COUNT(*) as t FROM users WHERE role='user'")->fetch_assoc()['t'];
$direct = $conn->query("SELECT COUNT(*) as t FROM single_order")->fetch_assoc()['t'];
$cart = $conn->query("SELECT COUNT(*) as t FROM orders")->fetch_assoc()['t'];
$order_count = $direct + $cart;

$revenue_query = "SELECT 
    (IFNULL((SELECT SUM(total_amount) FROM orders), 0) + 
     IFNULL((SELECT SUM(total_amount) FROM single_order), 0)) as lifetime_revenue";
$revenue_res = $conn->query($revenue_query);
$revenue = $revenue_res->fetch_assoc()['lifetime_revenue'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KickFit | Admin Console</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --sidebar-dark: #1a1c1e;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --transition: all 0.3s ease;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', system-ui, sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); display: flex; }

        /* SIDEBAR */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-dark); position: fixed; padding: 30px 0; display: flex; flex-direction: column; z-index: 1000; }
        .sidebar-brand { color: var(--white); font-size: 22px; font-weight: 800; text-align: center; margin-bottom: 40px; letter-spacing: 1px; }
        .sidebar-brand span { color: var(--primary); }
        .sidebar-nav { list-style: none; flex: 1; }
        .sidebar-nav li { padding: 5px 20px; }
        .sidebar-nav a { display: flex; align-items: center; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: var(--transition); }
        .sidebar-nav a i { width: 25px; font-size: 18px; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(192, 57, 43, 0.1); color: var(--primary); }
        .logout-box { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .logout-btn { color: #ff4757; text-decoration: none; font-size: 14px; font-weight: 600; }

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .welcome-msg h1 { font-size: 28px; font-weight: 800; color: var(--text-dark); }
        .welcome-msg p { color: var(--text-muted); margin-top: 5px; }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; transition: var(--transition); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .stat-info h3 { font-size: 13px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
        .stat-info .number { font-size: 26px; font-weight: 800; margin-top: 5px; color: var(--text-dark); }
        .stat-icon { width: 50px; height: 50px; background: rgba(192, 57, 43, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 20px; }

        /* DASHBOARD GRID */
        .dashboard-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px; }
        .card { background: var(--white); padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; }
        .card h2 { font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .edit-btn { position: absolute; top: 25px; right: 25px; background: var(--primary); color: #fff; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600; }

        .profile-list { list-style: none; }
        .profile-list li { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .profile-list li:last-child { border-bottom: none; }
        .label { color: var(--text-muted); font-size: 14px; }
        .value { font-weight: 600; color: var(--text-dark); }

        .quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .action-btn { background: var(--bg-light); padding: 20px; border-radius: 12px; text-align: center; text-decoration: none; color: var(--text-dark); font-weight: 600; font-size: 13px; transition: var(--transition); border: 1px solid transparent; }
        .action-btn:hover { background: var(--white); border-color: var(--primary); color: var(--primary); }
        .action-btn i { display: block; font-size: 22px; margin-bottom: 10px; }

        /* MODAL STYLES */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: #fff; padding: 30px; border-radius: 16px; width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { font-size: 20px; }
        .close-modal { cursor: pointer; font-size: 20px; color: var(--text-muted); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 5px; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .save-btn { width: 100%; background: var(--primary); color: #fff; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }

        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">KICK<span>FIT</span></div>
    <ul class="sidebar-nav">
        <li><a href="#" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
        <li><a href="displayproduct.php"><i class="fa-solid fa-box"></i> Inventory</a></li>
        <li><a href="vieworders.php"><i class="fa-solid fa-cart-shopping"></i> Orders</a></li>
        <li><a href="analytics.php"><i class="fa-solid fa-chart-line"></i> Analytics</a></li>
        <li><a href="../index.php"><i class="fa-solid fa-globe"></i> Visit Site</a></li>
    </ul>
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
    </div>
</aside>

<main class="main-content">
    <header class="header-flex">
        <div class="welcome-msg">
            <h1>Admin Console</h1>
            <p>Welcome back, <?= explode(' ', $admin['name'])[0] ?>. Here's what's happening today.</p>
        </div>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Products</h3>
                <div class="number"><?= $product_count ?></div>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3>Total Users</h3>
                <div class="number"><?= $user_count ?></div>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3>Orders</h3>
                <div class="number"><?= $order_count ?></div>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-bag-shopping"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3>Overall Revenue</h3>
                <div class="number">₹<?= number_format($revenue, 2) ?></div>
            </div>
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        </div>
    </section>

    <div class="dashboard-grid">
        <section class="card">
            <button class="edit-btn" onclick="openModal()"><i class="fa-solid fa-pen-to-square"></i> Edit Profile</button>
            <h2><i class="fa-solid fa-user-shield" style="color:var(--primary)"></i> Admin Profile</h2>
            <ul class="profile-list">
                <li><span class="label">Full Name</span> <span class="value"><?= $admin['name'] ?></span></li>
                <li><span class="label">Email Address</span> <span class="value"><?= $admin['email'] ?></span></li>
                <li><span class="label">Contact</span> <span class="value"><?= $admin['phone'] ?></span></li>
                <li><span class="label">Address</span> <span class="value"><?= $admin['address'] ?></span></li>
                <li><span class="label">Account Role</span> <span class="value" style="color:var(--primary)">ADMINISTRATOR</span></li>
            </ul>
        </section>

        <section class="card">
            <h2><i class="fa-solid fa-bolt" style="color:#f1c40f"></i> Quick Actions</h2>
            <div class="quick-actions">
                <a href="addproduct.php" class="action-btn"><i class="fa-solid fa-plus"></i> Add Product</a>
                <a href="analytics.php" class="action-btn"><i class="fa-solid fa-chart-pie"></i> Reports</a>
                <a href="vieworders.php" class="action-btn"><i class="fa-solid fa-truck"></i> Ship Orders</a>
                <a href="../index.php" class="action-btn"><i class="fa-solid fa-eye"></i> View Store</a>
            </div>
        </section>
    </div>
</main>

<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Update Details</h2>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <form action="update_admin.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= $admin['name'] ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= $admin['email'] ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= $admin['phone'] ?>" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="3" required><?= $admin['address'] ?></textarea>
            </div>
            <button type="submit" class="save-btn">Update Profile</button>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('editModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('editModal').style.display = 'none'; }
    
    // Close modal if user clicks outside of it
    window.onclick = function(event) {
        let modal = document.getElementById('editModal');
        if (event.target == modal) { modal.style.display = "none"; }
    }

    // Check for success/error messages in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) { alert('Profile updated successfully!'); }
    if (urlParams.has('error')) { alert('Failed to update profile.'); }
</script>

</body>
</html>