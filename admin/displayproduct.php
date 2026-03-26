<?php
include "../db.php";
session_start();

/* ---------- ACCESS CONTROL ---------- */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== "admin") {
    header("Location: ../index.php");
    exit();
}

/* ---------- FETCH PRODUCTS ---------- */
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory | KickFit Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --sidebar-dark: #1a1c1e;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --success: #27ae60;
            --danger: #e74c3c;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); display: flex; }

        /* SIDEBAR (Unified Style) */
        .sidebar {
            width: 260px; height: 100vh; background: var(--sidebar-dark);
            position: fixed; padding: 30px 0; display: flex; flex-direction: column;
        }
        .sidebar-brand { color: var(--white); font-size: 22px; font-weight: 800; text-align: center; margin-bottom: 40px; }
        .sidebar-brand span { color: var(--primary); }
        .sidebar-nav { list-style: none; flex: 1; }
        .sidebar-nav li { padding: 5px 20px; }
        .sidebar-nav a {
            display: flex; align-items: center; padding: 12px 15px; color: #94a3b8;
            text-decoration: none; border-radius: 8px; font-size: 14px; transition: var(--transition);
        }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(192, 57, 43, 0.1); color: var(--primary); }
        .sidebar-nav a i { width: 30px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 26px; font-weight: 800; }
        
        .add-btn {
            background: var(--primary); color: white; text-decoration: none;
            padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600;
            display: flex; align-items: center; gap: 8px; transition: var(--transition);
        }
        .add-btn:hover { background: #922b21; transform: translateY(-2px); }

        /* TABLE STYLING */
        .table-container {
            background: var(--white); border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        
        thead tr { background: #f1f5f9; border-bottom: 2px solid #e2e8f0; }
        th { padding: 18px 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; }

        tbody tr { border-bottom: 1px solid #f1f5f9; transition: var(--transition); }
        tbody tr:hover { background: #fcfcfd; }

        td { padding: 15px 20px; vertical-align: middle; font-size: 14px; }

        /* PRODUCT IMAGE */
        .prod-img {
            width: 60px; height: 60px; object-fit: cover;
            border-radius: 10px; border: 1px solid #eee;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* STOCK BADGE */
        .badge {
            padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; animation: pulse 2s infinite; }

        @keyframes pulse {
            0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; }
        }

        /* ACTIONS */
        .action-btns { display: flex; gap: 10px; }
        .btn-icon {
            width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;
            border-radius: 8px; text-decoration: none; transition: var(--transition);
        }
        .btn-edit { background: #eff6ff; color: #2563eb; }
        .btn-edit:hover { background: #2563eb; color: white; }
        .btn-delete { background: #fff1f2; color: #e11d48; }
        .btn-delete:hover { background: #e11d48; color: white; }

        .desc-cell { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #64748b; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">KICK<span>FIT</span></div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="addproduct.php"><i class="fa-solid fa-plus-circle"></i> Add Product</a></li>
        <li><a href="displayproduct.php" class="active"><i class="fa-solid fa-list"></i> View Products</a></li>
        <li><a href="vieworders.php"><i class="fa-solid fa-truck"></i> Orders</a></li>
        <li><a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1>Inventory Management</h1>
            <p style="color: #64748b; font-size: 14px;">Review and manage your sneaker stock levels.</p>
        </div>
        <a href="addproduct.php" class="add-btn"><i class="fa-solid fa-plus"></i> New Product</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th style="text-align: right;">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <img src="../image/<?php echo $row['image'] ?>" class="prod-img" onerror="this.src='https://placehold.co/100x100?text=Sneaker'">
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--text-dark);"><?php echo $row['name'] ?></div>
                        <div class="desc-cell"><?php echo $row['description'] ?></div>
                    </td>
                    <td><span style="color: #64748b; font-weight: 500;"><?php echo ucfirst($row['category_name']) ?></span></td>
                    <td style="font-weight: 600;">₹<?php echo number_format($row['price'], 2) ?></td>
                    <td>
                        <?php if($row['stock'] > 5): ?>
                            <span class="badge badge-success"><?php echo $row['stock'] ?> In Stock</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><?php echo $row['stock'] ?> Low Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content: flex-end;">
                            <a href="updateproduct.php?product_id=<?php echo $row['id'] ?>" class="btn-icon btn-edit" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="deleteproduct.php?product_id=<?php echo $row['id'] ?>" class="btn-icon btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>