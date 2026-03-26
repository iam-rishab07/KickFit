<?php
session_start();
include "../db.php";

/* ---------- ACCESS CONTROL ---------- */
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "admin"){
    header("Location: ../index.php");
    exit();
}

/* ---------- FETCH ALL ORDERS (Grouped logic) ---------- */
$sql = "SELECT 
            o.id AS order_id,
            o.order_date,
            o.total_amount,
            u.name AS user_name,
            u.email AS user_email,
            pr.name AS product_name,
            pr.image AS product_image,
            pr.price AS unit_price,
            oi.quantity,
            CASE 
                WHEN (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) = 1 THEN 'Direct Buy'
                ELSE 'Cart'
            END AS source
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN users u ON o.user_id = u.id
        JOIN products pr ON oi.product_id = pr.id
        ORDER BY o.id DESC";

$result = mysqli_query($conn, $sql);

// Group the products by Order ID in a PHP Array
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $oid = $row['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'order_date'   => $row['order_date'],
            'total_amount' => $row['total_amount'],
            'user_name'    => $row['user_name'],
            'user_email'   => $row['user_email'],
            'source'       => $row['source'],
            'items'        => []
        ];
    }
    $orders[$oid]['items'][] = [
        'name'     => $row['product_name'],
        'image'    => $row['product_image'],
        'price'    => $row['unit_price'],
        'quantity' => $row['quantity']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | KickFit Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --sidebar-dark: #1a1c1e;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --transition: all 0.3s ease;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); display: flex; }

        /* SIDEBAR */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-dark); position: fixed; padding: 30px 0; display: flex; flex-direction: column; z-index: 1000; }
        .sidebar-brand { color: var(--white); font-size: 22px; font-weight: 800; text-align: center; margin-bottom: 40px; }
        .sidebar-brand span { color: var(--primary); }
        .sidebar-nav { list-style: none; flex: 1; }
        .sidebar-nav li { padding: 5px 20px; }
        .sidebar-nav a { display: flex; align-items: center; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 8px; font-size: 14px; transition: var(--transition); }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(192, 57, 43, 0.1); color: var(--primary); }
        .sidebar-nav a i { width: 30px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 26px; font-weight: 800; }

        /* GROUPED ORDER CARDS */
        .order-card {
            background: var(--white); border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 15px;
            border: 1px solid #edf2f7; overflow: hidden;
        }

        .order-header {
            padding: 20px; display: grid; grid-template-columns: 1fr 1.5fr 1fr 1fr 0.5fr;
            align-items: center; cursor: pointer; transition: background 0.2s;
        }
        .order-header:hover { background: #fcfcfd; }

        .order-id { font-family: monospace; font-weight: 700; color: #64748b; }
        .customer-info b { display: block; font-size: 14px; }
        .customer-info span { font-size: 12px; color: #94a3b8; }
        
        .order-total b { color: var(--primary); font-size: 16px; }
        .order-total span { display: block; font-size: 11px; color: #94a3b8; }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-direct { background: #e0f2fe; color: #0369a1; }
        .badge-cart { background: #fef3c7; color: #92400e; }

        /* DROPDOWN ITEMS */
        .order-details {
            display: none; background: #f9fafb; padding: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .order-details.active { display: block; }

        .item-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid #eee;
        }
        .item-row:last-child { border-bottom: none; }
        .item-img { width: 45px; height: 45px; border-radius: 6px; object-fit: cover; border: 1px solid #ddd; }
        
        .item-meta { flex: 1; padding-left: 15px; }
        .item-meta b { font-size: 14px; }
        .item-meta p { font-size: 12px; color: #64748b; }

        .item-price-calc { text-align: right; font-size: 14px; }

        .chevron { transition: transform 0.3s; color: #cbd5e0; }
        .active-card .chevron { transform: rotate(180deg); }

        @media (max-width: 1024px) {
            .order-header { grid-template-columns: 1fr 1fr; gap: 15px; }
            .order-header .source, .order-header .chevron { display: none; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">KICK<span>FIT</span></div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
        <li><a href="displayproduct.php"><i class="fa-solid fa-list"></i> View Products</a></li>
        <li><a href="vieworders.php" class="active"><i class="fa-solid fa-truck-fast"></i> View Orders</a></li>
        <li><a href="analytics.php"><i class="fa-solid fa-chart-line"></i> Analytics</a></li>
        <li><a href="../logout.php"><i class="fa-solid fa-power-off"></i> Logout</a></li>
    </ul>
</aside>

<main class="main-content">
    <div class="page-header">
        <h1>Order Management</h1>
        <p style="color: #64748b;">Click on any order to view individual items and pricing.</p>
    </div>

    <?php if(empty($orders)): ?>
        <div style="text-align:center; padding:50px; background:white; border-radius:12px;">
            <p style="color:#64748b;">No orders found in the system.</p>
        </div>
    <?php else: ?>
        
        <?php foreach($orders as $id => $data): ?>
        <div class="order-card" id="card-<?= $id ?>">
            <div class="order-header" onclick="toggleOrder(<?= $id ?>)">
                <div class="order-id">#ORD-<?= $id ?></div>
                
                <div class="customer-info">
                    <b><?= $data['user_name'] ?></b>
                    <span><?= $data['user_email'] ?></span>
                </div>

                <div class="order-total">
                    <b>₹<?= number_format($data['total_amount'], 2) ?></b>
                    <span><?= count($data['items']) ?> Item(s)</span>
                </div>

               

                <div style="text-align:right;">
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </div>
            </div>

            <div class="order-details" id="details-<?= $id ?>">
                <div style="margin-bottom: 10px; font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">
                    Placed on: <?= date('d M, Y | h:i A', strtotime($data['order_date'])) ?>
                </div>
                
                <?php foreach($data['items'] as $item): ?>
                <div class="item-row">
                    <div style="display:flex; align-items:center;">
                        <img src="../image/<?= $item['image'] ?>" class="item-img">
                        <div class="item-meta">
                            <b><?= $item['name'] ?></b>
                            <p>Unit Price: ₹<?= number_format($item['price'], 2) ?></p>
                        </div>
                    </div>
                    <div class="item-price-calc">
                        <b>x <?= $item['quantity'] ?></b>
                        <p style="font-weight:700;">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #e2e8f0; text-align: right;">
                    <span style="font-size: 13px; color: #64748b;">Order Grand Total: </span>
                    <b style="font-size: 20px; color: var(--primary); margin-left: 10px;">₹<?= number_format($data['total_amount'], 2) ?></b>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</main>

<script>
function toggleOrder(id) {
    const details = document.getElementById('details-' + id);
    const card = document.getElementById('card-' + id);
    
    // Toggle active classes
    details.classList.toggle('active');
    card.classList.toggle('active-card');
}
</script>

</body>
</html>