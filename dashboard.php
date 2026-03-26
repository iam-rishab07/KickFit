<?php
session_start();
include "db.php";

/* ---------- ACCESS CONTROL (Prepared) ---------- */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check for Admin redirection
if($_SESSION['user_role'] == "admin") {
    header("Location: admin/dashboard.php");
    exit();
}

/* ---------- FETCH USER INFO (Prepared) ---------- */
$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* ---------- UPDATED USER STATS ---------- */
$stmt_orders = $conn->prepare("SELECT 
    ((SELECT COUNT(*) FROM single_order WHERE user_id = ?) + 
     (SELECT COUNT(*) FROM orders WHERE user_id = ?)) as total");
$stmt_orders->bind_param("ii", $user_id, $user_id);
$stmt_orders->execute();
$order_count = $stmt_orders->get_result()->fetch_assoc()['total'];

$stmt_spent = $conn->prepare("SELECT 
    (IFNULL((SELECT SUM(total_amount) FROM orders WHERE user_id = ?), 0) + 
     IFNULL((SELECT SUM(total_amount) FROM single_order WHERE user_id = ?), 0)) as overall_total");
$stmt_spent->bind_param("ii", $user_id, $user_id);
$stmt_spent->execute();
$total_spent = $stmt_spent->get_result()->fetch_assoc()['overall_total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard | KickFit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --dark: #1a1c1e;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --transition: all 0.3s ease;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', system-ui, sans-serif; }
        body { background: var(--light-bg); color: var(--text-main); display: flex; }

        /* SIDEBAR */
        .sidebar { width: 260px; height: 100vh; background: var(--dark); position: fixed; padding: 30px 0; display: flex; flex-direction: column; z-index: 1000; }
        .brand { color: var(--white); font-size: 24px; font-weight: 900; text-align: center; margin-bottom: 40px; letter-spacing: -1px; }
        .brand span { color: var(--primary); }
        .sidebar-nav { list-style: none; flex: 1; }
        .sidebar-nav li { padding: 5px 20px; }
        .sidebar-nav a { display: flex; align-items: center; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: var(--transition); }
        .sidebar-nav a:hover { background: rgba(255, 255, 255, 0.05); color: var(--white); }
        .sidebar-nav a.active { background: rgba(192, 57, 43, 0.1); color: var(--primary); }
        .sidebar-nav a i { width: 30px; font-size: 18px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 50px; }
        .welcome-card { margin-bottom: 40px; }
        .welcome-card h1 { font-size: 32px; font-weight: 800; color: var(--dark); }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; transition: var(--transition); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .stat-info h3 { font-size: 13px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
        .stat-info .number { font-size: 28px; font-weight: 800; margin-top: 5px; color: var(--dark); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }

        /* PROFILE SECTION */
        .profile-container { display: grid; grid-template-columns: 1fr 1.5fr; gap: 25px; }
        .card { background: var(--white); padding: 30px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); position: relative; }
        .card h2 { font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        .info-row { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #f1f5f9; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--text-muted); font-size: 14px; }
        .info-value { font-weight: 600; color: var(--text-dark); }

        .btn-edit { position: absolute; top: 25px; right: 25px; background: none; border: 1px solid #ddd; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; color: var(--text-muted); transition: 0.3s; }
        .btn-edit:hover { background: var(--primary); color: white; border-color: var(--primary); }

        .quick-link { display: block; width: 100%; padding: 15px; background: var(--light-bg); border-radius: 12px; margin-bottom: 10px; text-decoration: none; color: var(--text-dark); font-weight: 600; transition: var(--transition); }
        .quick-link:hover { background: var(--primary); color: #fff; }

        /* MODAL */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 450px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 5px; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
        .save-btn { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar .brand, .sidebar-nav span { display: none; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .profile-container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">KICK<span>FIT</span></div>
    <ul class="sidebar-nav">
        <li><a href="index.php"><i class="fa-solid fa-house"></i> <span>Home</span></a></li>
        <li><a href="myorders.php"><i class="fa-solid fa-bag-shopping"></i> <span>My Orders</span></a></li>
        <li><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> <span>My Cart</span></a></li>
        <li><a href="logout.php" style="color: #ef4444;"><i class="fa-solid fa-power-off"></i> <span>Logout</span></a></li>
    </ul>
</aside>

<main class="main-content">
    <header class="welcome-card">
        <h1>Hello, <?= explode(' ', $user['name'])[0] ?>! 👋</h1>
        <p style="color: var(--text-muted);">Welcome to your overall dashboard overview.</p>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Lifetime Orders</h3>
                <div class="number"><?= $order_count ?></div>
            </div>
            <div class="stat-icon" style="background: rgba(192, 57, 43, 0.1); color: var(--primary);"><i class="fa-solid fa-box"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Overall Total Spent</h3>
                <div class="number">₹<?= number_format($total_spent, 2) ?></div>
            </div>
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fa-solid fa-wallet"></i></div>
        </div>
    </section>

    <div class="profile-container">
        <section class="card">
            <h2><i class="fa-solid fa-bolt" style="color: #f1c40f;"></i> Quick Actions</h2>
            <a href="index.php#shop" class="quick-link"><i class="fa-solid fa-store"></i> Shop New Drops</a>
            <a href="myorders.php" class="quick-link"><i class="fa-solid fa-clock-rotate-left"></i> Order History</a>
            <a href="cart.php" class="quick-link"><i class="fa-solid fa-basket-shopping"></i> Go to Cart</a>
        </section>

        <section class="card">
            <button class="btn-edit" onclick="openModal()"><i class="fa-solid fa-user-pen"></i> Edit</button>
            <h2><i class="fa-solid fa-user-circle" style="color: var(--primary);"></i> Personal Profile</h2>
            <div class="info-row"><span class="info-label">Full Name</span> <span class="info-value"><?= htmlspecialchars($user['name']) ?></span></div>
            <div class="info-row"><span class="info-label">Email</span> <span class="info-value"><?= htmlspecialchars($user['email']) ?></span></div>
            <div class="info-row"><span class="info-label">Phone</span> <span class="info-value"><?= htmlspecialchars($user['phone']) ?></span></div>
            <div class="info-row"><span class="info-label">Delivery Address</span> <span class="info-value"><?= htmlspecialchars($user['address']) ?></span></div>
        </section>
    </div>
</main>

<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Profile</h3>
            <i class="fa-solid fa-xmark" onclick="closeModal()" style="cursor:pointer"></i>
        </div>
        <form action="update_profile.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <button type="submit" name="update" class="save-btn">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('editModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('editModal').style.display = 'none'; }
    
    // Close modal if user clicks outside of content
    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) { closeModal(); }
    }
</script>

</body>
</html>