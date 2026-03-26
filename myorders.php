<?php
include "db.php";
session_start();

/* ---------- ACCESS CONTROL ---------- */
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if($_SESSION['user_role'] != "user"){
    header("Location: admin/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------- FETCH ORDERS (Grouped logic) ---------- */
// We fetch all items, but we will group them in PHP
$sql = "SELECT o.id AS order_id, o.total_amount, o.order_date,
               pr.name AS product_name, pr.image, pr.price AS unit_price,
               oi.quantity AS qty
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products pr ON oi.product_id = pr.id
        WHERE o.user_id = ?
        ORDER BY o.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

// Organize items by Order ID
$orders = [];
while($row = $res->fetch_assoc()){
    $oid = $row['order_id'];
    if(!isset($orders[$oid])){
        $orders[$oid] = [
            'total' => $row['total_amount'],
            'date' => $row['order_date'],
            'items' => []
        ];
    }
    $orders[$oid]['items'][] = [
        'name' => $row['product_name'],
        'image' => $row['image'],
        'qty' => $row['qty'],
        'price' => $row['unit_price']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Order History | KickFit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --sidebar-dark: #1a1c1e;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --success: #27ae60;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); display: flex; }

        /* SIDEBAR */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-dark); position: fixed; padding: 30px 0; display: flex; flex-direction: column; z-index: 1000; }
        .brand { color: var(--white); font-size: 24px; font-weight: 900; text-align: center; margin-bottom: 40px; }
        .brand span { color: var(--primary); }
        .sidebar-nav { list-style: none; }
        .sidebar-nav li { padding: 5px 20px; }
        .sidebar-nav a { display: flex; align-items: center; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 8px; font-size: 14px; transition: 0.3s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(192, 57, 43, 0.1); color: var(--primary); }
        .sidebar-nav a i { width: 30px; font-size: 18px; }

        /* MAIN */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 50px; }
        .header-title { margin-bottom: 30px; }
        .header-title h1 { font-size: 28px; font-weight: 800; }

        /* GROUPED ORDER CARD */
        .order-card {
            background: var(--white); border-radius: 16px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px;
            overflow: hidden; border: 1px solid #edf2f7;
        }

        .order-header {
            padding: 20px; background: #fff; display: flex;
            justify-content: space-between; align-items: center;
            cursor: pointer; transition: background 0.2s;
        }
        .order-header:hover { background: #fcfcfd; }

        .order-meta b { font-family: monospace; color: var(--primary); font-size: 16px; }
        .order-meta span { font-size: 12px; color: #94a3b8; margin-left: 10px; }
        
        .order-summary-price { text-align: right; }
        .order-summary-price .amt { display: block; font-weight: 800; font-size: 18px; color: var(--text-dark); }
        .order-summary-price .count { font-size: 12px; color: #64748b; }

        /* DROPDOWN DETAILS */
        .order-details {
            padding: 0 20px 20px; background: #fafafa;
            border-top: 1px solid #f1f5f9; display: none; /* Controlled by JS */
        }
        .order-details.open { display: block; }

        .item-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 15px 0; border-bottom: 1px solid #eee;
        }
        .item-row:last-child { border-bottom: none; }

        .prod-info { display: flex; align-items: center; gap: 15px; }
        .prod-info img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .prod-name { font-weight: 600; font-size: 14px; }
        
        .item-pricing { text-align: right; font-size: 14px; }
        .item-pricing span { color: #64748b; margin-right: 15px; }

        .status-pill {
            display: inline-block; background: rgba(39, 174, 96, 0.1); color: var(--success);
            padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 700; margin-top: 5px;
        }

        .empty-state { text-align: center; padding: 60px; background: white; border-radius: 16px; }

        /* ARROW ANIMATION */
        .fa-chevron-down { transition: transform 0.3s; color: #cbd5e0; }
        .open .fa-chevron-down { transform: rotate(180deg); }

        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar .brand, .sidebar-nav span { display: none; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); padding: 30px; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">KICK<span>FIT</span></div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a></li>
        <li><a href="myorders.php" class="active"><i class="fa-solid fa-clock-rotate-left"></i> <span>My Orders</span></a></li>
        <li><a href="index.php"><i class="fa-solid fa-store"></i> <span>Continue Shop</span></a></li>
        <li><a href="logout.php" style="color:#ef4444"><i class="fa-solid fa-power-off"></i> <span>Logout</span></a></li>
    </ul>
</aside>

<main class="main-content">
    <div class="header-title">
        <h1>My Orders</h1>
        <p style="color: #64748b;">Click an order to view purchased items.</p>
    </div>

    <?php if(empty($orders)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-box-open" style="font-size: 40px; color: #ddd; margin-bottom: 20px;"></i>
            <h3>No orders found.</h3>
            <a href="index.php" style="display:inline-block; margin-top:20px; color:var(--primary); font-weight:700; text-decoration:none;">Go Shopping &rarr;</a>
        </div>
    <?php else: ?>
        
        <?php foreach($orders as $id => $data): ?>
        <div class="order-card" id="order-<?php echo $id; ?>">
            <div class="order-header" onclick="toggleOrder(<?php echo $id; ?>)">
                <div class="order-meta">
                    <b>#KFT-<?php echo $id; ?></b>
                    <span>Ordered on <?php echo date('d M, Y', strtotime($data['date'])); ?></span>
                    <br>
                    <span class="status-pill"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                </div>
                
                <div style="display:flex; align-items:center; gap:25px;">
                    <div class="order-summary-price">
                        <span class="amt">₹<?php echo number_format($data['total'], 2); ?></span>
                        <span class="count"><?php echo count($data['items']); ?> Item(s)</span>
                    </div>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

            <div class="order-details" id="details-<?php echo $id; ?>">
                <?php foreach($data['items'] as $item): ?>
                <div class="item-row">
                    <div class="prod-info">
                        <img src="image/<?php echo $item['image']; ?>" alt="">
                        <div class="prod-name"><?php echo $item['name']; ?></div>
                    </div>
                    <div class="item-pricing">
                        <span>₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['qty']; ?></span>
                        <strong>₹<?php echo number_format($item['price'] * $item['qty'], 2); ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div style="text-align:right; margin-top:15px; padding-top:10px; border-top:1px dashed #ddd;">
                    <small style="color:#64748b">Grand Total:</small>
                    <b style="font-size:18px; color:var(--primary); margin-left:10px;">₹<?php echo number_format($data['total'], 2); ?></b>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</main>

<script>
function toggleOrder(id) {
    const details = document.getElementById('details-' + id);
    const card = document.getElementById('order-' + id);
    
    // Toggle class for display
    details.classList.toggle('open');
    card.classList.toggle('open');
}
</script>

</body>
</html>