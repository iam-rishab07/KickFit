<?php
include("../db.php");

/* ===============================
   FILTERS & DATE LOGIC
=================================*/
$selectedYear  = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : date("n");

/* ===============================
   1. SALES ANALYTICS DATA
=================================*/
$monthlySales = array_fill(0, 12, 0);
$stmt = $conn->prepare("SELECT MONTH(order_date) as m, SUM(total_amount) as total FROM orders WHERE YEAR(order_date) = ? GROUP BY MONTH(order_date)");
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $monthlySales[$row['m'] - 1] = (float)$row['total']; }

// Query for filtered month's revenue
$stmtMonth = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE YEAR(order_date) = ? AND MONTH(order_date) = ?");
$stmtMonth->bind_param("ii", $selectedYear, $selectedMonth);
$stmtMonth->execute();
$filteredMonthRevenue = $stmtMonth->get_result()->fetch_assoc()['total'] ?? 0;

$bestProductQ = $conn->query("SELECT p.name, SUM(oi.quantity) as total_sold FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY p.id ORDER BY total_sold DESC LIMIT 1");
$bestProduct = $bestProductQ->fetch_assoc();

/* ===============================
   2. USER ANALYTICS DATA
=================================*/
$bestCustomerQ = $conn->query("SELECT u.name, u.email, SUM(o.total_amount) as total_spent FROM orders o JOIN users u ON o.user_id = u.id GROUP BY u.id ORDER BY total_spent DESC LIMIT 1");
$bestCustomer = $bestCustomerQ->fetch_assoc();

// User Search Logic
$userSearchRes = null;
$userSearchAttempted = false;
if(isset($_GET['user_query']) && !empty(trim($_GET['user_query']))){
    $userSearchAttempted = true;
    $q = "%".$_GET['user_query']."%";
    $stmt = $conn->prepare("SELECT u.*, (SELECT SUM(total_amount) FROM orders WHERE user_id = u.id) as spent FROM users u WHERE u.name LIKE ? OR u.email LIKE ? LIMIT 1");
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $userSearchRes = $stmt->get_result()->fetch_assoc();
}

// Fetch All Customers List
$allUsersQ = $conn->query("SELECT id, name, email, phone, role FROM users ORDER BY id DESC");

/* ===============================
   3. PRODUCT ANALYTICS DATA
=================================*/
$lowStockCount = $conn->query("SELECT COUNT(*) as t FROM products WHERE stock < 5")->fetch_assoc()['t'];

$chartProdNames = [];
$chartProdSales = [];
$pieQ = $conn->query("SELECT p.name, SUM(oi.quantity) as qty FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY p.id ORDER BY qty DESC LIMIT 5");
while($pieRow = $pieQ->fetch_assoc()){
    $chartProdNames[] = $pieRow['name'];
    $chartProdSales[] = $pieRow['qty'];
}

$prodSearchRes = null;
$prodSearchAttempted = false;
if(isset($_GET['prod_id']) && !empty($_GET['prod_id'])){
    $prodSearchAttempted = true;
    $stmt = $conn->prepare("SELECT p.*, (SELECT SUM(quantity) FROM order_items WHERE product_id = p.id) as sold FROM products p WHERE p.id = ?");
    $stmt->bind_param("i", $_GET['prod_id']);
    $stmt->execute();
    $prodSearchRes = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Analytics | KickFit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #4361ee; --dark: #2b2d42; --light: #f8f9fd; --danger: #f72585; --success: #4cc9f0; }
        body { font-family: 'Inter', sans-serif; background: var(--light); color: var(--dark); margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 30px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; background: #fff; padding: 10px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .tab-btn { padding: 12px 24px; border: none; background: none; cursor: pointer; font-weight: 600; color: #8d99ae; border-radius: 8px; transition: 0.3s; }
        .tab-btn.active { background: var(--primary); color: #fff; }
        .section { display: none; animation: fadeIn 0.4s ease; }
        .section.active { display: block; }
        .card { background: #fff; padding: 25px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input { flex: 1; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; outline: none; }
        .btn-primary { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; background: var(--primary); color: white; transition: 0.3s; }
        .result-card { background: #f0f4ff; border-left: 5px solid var(--primary); padding: 15px; border-radius: 8px; margin-top: 10px; }
        .not-found { color: var(--danger); background: #fff1f0; padding: 15px; border-radius: 8px; border: 1px solid #ffa39e; margin-top: 10px; font-weight: 600; }
        
        /* Table Styling */
        .user-table-container { max-height: 400px; overflow-y: auto; margin-top: 20px; border: 1px solid #eee; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th { position: sticky; top: 0; background: #f8f9fa; padding: 12px; text-align: left; font-size: 13px; border-bottom: 2px solid #eee; }
        td { padding: 12px; font-size: 14px; border-bottom: 1px solid #eee; }
        tr:hover { background: #fcfcfd; }
        .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .role-user { background: #e3f2fd; color: #1976d2; }
        .role-admin { background: #fff3e0; color: #f57c00; }

        .chart-flex { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .chart-flex { grid-template-columns: 1fr; } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1>Dashboard Intelligence</h1>
        <a href="dashboard.php" style="text-decoration:none; color:var(--primary); font-weight:700;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="showSection('sales', this)"><i class="fas fa-chart-line"></i> Sales</button>
        <button class="tab-btn" onclick="showSection('users', this)"><i class="fas fa-users"></i> Users</button>
        <button class="tab-btn" onclick="showSection('products', this)"><i class="fas fa-box"></i> Products</button>
    </div>

    <div id="sales" class="section active">
        <div class="kpi-grid">
            <div class="card">
                <h3 style="color:#8d99ae; font-size:12px; text-transform:uppercase;">Best Selling Product</h3>
                <p style="font-size:20px; font-weight:800; margin:10px 0; color:var(--primary);"><?= $bestProduct['name'] ?? 'N/A' ?></p>
                <small><?= $bestProduct['total_sold'] ?? 0 ?> units sold total</small>
            </div>
            <div class="card">
                <h3 style="color:#8d99ae; font-size:12px; text-transform:uppercase;">Monthly Filter</h3>
                <form method="GET">
                    <input type="hidden" name="tab" value="sales">
                    <select name="year"><?php for($y=2024;$y<=2026;$y++) echo "<option ".($y==$selectedYear?'selected':'').">$y</option>"; ?></select>
                    <select name="month"><?php for($m=1;$m<=12;$m++) echo "<option value='$m' ".($m==$selectedMonth?'selected':'').">".date("M", mktime(0,0,0,$m,1))."</option>"; ?></select>
                    <button type="submit" class="btn-primary" style="padding:5px 10px; border-radius:4px; border:none;">Go</button>
                </form>
                <div style="margin-top:10px;">
                    <small>Revenue: <strong>₹<?= number_format($filteredMonthRevenue, 2) ?></strong></small>
                </div>
            </div>
        </div>
        <div class="card">
            <canvas id="salesChart" height="100"></canvas>
        </div>
    </div>

    <div id="users" class="section">
        <div class="kpi-grid">
            <div class="card" style="border-top: 4px solid var(--success);">
                <h3 style="color:#8d99ae; font-size:12px; text-transform:uppercase;">Top Spender</h3>
                <p style="font-size:20px; font-weight:800; margin:10px 0;"><?= $bestCustomer['name'] ?? 'N/A' ?></p>
                <p style="color:var(--success); font-weight:700;">₹<?= number_format($bestCustomer['total_spent'] ?? 0, 2) ?></p>
            </div>
            <div class="card">
                <h3>Find Customer</h3>
                <form class="search-box" method="GET">
                    <input type="hidden" name="tab" value="users">
                    <input type="text" name="user_query" placeholder="Name or Email..." value="<?= htmlspecialchars($_GET['user_query'] ?? '') ?>">
                    <button class="btn btn-primary">Search</button>
                </form>
                <?php if($userSearchRes): ?>
                    <div class="result-card">
                        <strong><?= $userSearchRes['name'] ?></strong> (<?= $userSearchRes['email'] ?>)<br>
                        <small>Contact: <?= $userSearchRes['phone'] ?> | Spent: ₹<?= number_format($userSearchRes['spent'] ?? 0, 2) ?></small>
                    </div>
                <?php elseif($userSearchAttempted): ?>
                    <div class="not-found">User not found.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h3>Registered Customers List</h3>
            <div class="user-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u = $allUsersQ->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td><strong><?= $u['name'] ?></strong></td>
                            <td><?= $u['email'] ?></td>
                            <td><?= $u['phone'] ?></td>
                            <td>
                                <span class="role-badge <?= $u['role'] == 'admin' ? 'role-admin' : 'role-user' ?>">
                                    <?= $u['role'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="products" class="section">
        <div class="chart-flex">
            <div class="left-col">
                <div class="card" style="border-top: 4px solid var(--danger);">
                    <h3 style="color:#8d99ae; font-size:12px; text-transform:uppercase;">Critical Stock</h3>
                    <p style="font-size:32px; font-weight:800; margin:10px 0; color:var(--danger);"><?= $lowStockCount ?></p>
                    <small>Items with less than 5 units left</small>
                </div>
                <div class="card">
                    <h3>Product Lookup</h3>
                    <form class="search-box" method="GET">
                        <input type="hidden" name="tab" value="products">
                        <input type="number" name="prod_id" placeholder="ID..." value="<?= htmlspecialchars($_GET['prod_id'] ?? '') ?>">
                        <button class="btn btn-primary">Locate</button>
                    </form>
                    <?php if($prodSearchRes): ?>
                        <div class="result-card">
                            <div style="display:flex; gap:15px; align-items:center;">
                                <img src="../image/<?= $prodSearchRes['image'] ?>" width="60" height="60" style="border-radius:8px; object-fit:cover;">
                                <div>
                                    <strong><?= $prodSearchRes['name'] ?></strong><br>
                                    <small>Sold: <?= $prodSearchRes['sold'] ?? 0 ?> units | Stock: <?= $prodSearchRes['stock'] ?></small>
                                </div>
                            </div>
                        </div>
                    <?php elseif($prodSearchAttempted): ?>
                        <div class="not-found">Product not found.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card" style="display: flex; flex-direction: column; align-items: center;">
                <h3>Top 5 Products Sales</h3>
                <div style="width: 100%; max-width: 280px;">
                    <canvas id="productPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(id, btn) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    if(btn) btn.classList.add('active');
    const url = new URL(window.location);
    url.searchParams.set('tab', id);
    window.history.pushState({}, '', url);
}

const activeTab = new URLSearchParams(window.location.search).get('tab') || 'sales';
const targetBtn = document.querySelector(`[onclick*="${activeTab}"]`);
if(targetBtn) showSection(activeTab, targetBtn);

new Chart(document.getElementById('salesChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
        datasets: [{
            label: 'Revenue',
            data: <?= json_encode($monthlySales) ?>,
            borderColor: '#4361ee',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            fill: true, tension: 0.4
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('productPieChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($chartProdNames) ?>,
        datasets: [{
            data: <?= json_encode($chartProdSales) ?>,
            backgroundColor: ['#4361ee', '#4cc9f0', '#f72585', '#7209b7', '#3a0ca3'],
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
    }
});
</script>
</body>
</html>