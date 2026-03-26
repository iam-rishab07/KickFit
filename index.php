<?php
session_start();
include "db.php";

/* ================= BUY NOW LOGIC ================= */
if(isset($_POST['buy_now']) && isset($_SESSION['user_id'])){
    $user_id    = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']);

    // Use Prepared Statements for security
    $stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_data = $stmt->get_result()->fetch_assoc();

    if($product_data){
        $price = $product_data['price'];
        $stock = $product_data['stock'];

        if($quantity > 0 && $quantity <= $stock){
            $total_amount = $price * $quantity;

            // 1. Create Order
            $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_date) VALUES (?, ?, NOW())");
            $stmt_order->bind_param("id", $user_id, $total_amount);
            
            if($stmt_order->execute()){
                $order_id = $conn->insert_id;

                // 2. Insert Item
                $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt_item->bind_param("iii", $order_id, $product_id, $quantity);
                $stmt_item->execute();

                // 3. Update Stock
                $new_stock = $stock - $quantity;
                $stmt_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $stmt_stock->bind_param("ii", $new_stock, $product_id);
                $stmt_stock->execute();

                echo "<script>alert('🔥 Order Placed! Get ready to flex.'); window.location='index.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Sold out or invalid quantity!');</script>";
        }
    }
}

/* ================= DATA FETCHING ================= */
$cat_filter = isset($_GET['category_name']) ? $_GET['category_name'] : null;

if($cat_filter) {
    $p_stmt = $conn->prepare("SELECT * FROM products WHERE category_name = ? AND stock > 0");
    $p_stmt->bind_param("s", $cat_filter);
} else {
    $p_stmt = $conn->prepare("SELECT * FROM products WHERE stock > 0");
}
$p_stmt->execute();
$products = $p_stmt->get_result();

$categories = $conn->query("SELECT name FROM categories");

// Cart Count
$cart_count = 0;
if(isset($_SESSION['user_id'])){
    $u_id = $_SESSION['user_id'];
    $cart_res = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id='$u_id'");
    $cart_count = $cart_res->fetch_assoc()['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KickFit — Elite Sneakers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --accent: #ff3e3e;
            --dark: #0f1113;
            --gray: #f8f9fa;
            --text-main: #1d1d1f;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Roboto, sans-serif; }
        body { background: var(--gray); color: var(--text-main); line-height: 1.6; }

        /* Header Navigation */
        .header { 
            position: fixed; top: 0; width: 100%; z-index: 1000; 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 8%; background: rgba(255,255,255,0.9); 
            backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .logo img { height: 40px; transition: 0.3s; }
        .nav ul { display: flex; list-style: none; align-items: center; gap: 30px; }
        .nav a { text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .nav a:hover { color: var(--accent); }

        /* Cart Icon */
        .cart-link { position: relative; font-size: 1.2rem; }
        .cart-badge { 
            position: absolute; top: -10px; right: -12px; 
            background: var(--accent); color: #fff; 
            font-size: 10px; border-radius: 50%; padding: 2px 6px; 
        }

        /* Hero Section */
        .hero { 
            margin-top: 70px; height: 70vh; 
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image/puma.jpg') center/cover no-repeat;
            display: flex; align-items: center; justify-content: center; text-align: center; color: #fff;
        }
        .hero-content h1 { font-size: 3.5rem; text-transform: uppercase; font-weight: 900; letter-spacing: -1px; }
        .hero-btn { 
            display: inline-block; margin-top: 20px; padding: 15px 35px; 
            background: #fff; color: #000; text-decoration: none; 
            font-weight: 800; border-radius: 4px; transition: 0.3s; 
        }
        .hero-btn:hover { background: var(--accent); color: #fff; transform: scale(1.05); }

        /* Category Strip */
        .cat-strip { 
            display: flex; justify-content: center; gap: 15px; padding: 25px; 
            background: #fff; overflow-x: auto; 
        }
        .cat-item { 
            padding: 8px 20px; border: 1px solid #ddd; border-radius: 50px; 
            text-decoration: none; color: #555; font-weight: 600; font-size: 14px;
            transition: 0.3s; white-space: nowrap;
        }
        .cat-item:hover, .cat-item.active { background: var(--dark); color: #fff; border-color: var(--dark); }

        /* Product Grid */
        .container { max-width: 1300px; margin: 0 auto; padding: 40px 20px; }
        .grid-title { margin-bottom: 30px; font-size: 24px; font-weight: 800; text-transform: uppercase; }
        
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
        .p-card { 
            background: #fff; border-radius: 8px; overflow: hidden; 
            transition: 0.3s; position: relative; border: 1px solid #eee;
        }
        .p-card:hover { transform: translateY(-10px); box-shadow: 0 20px 30px rgba(0,0,0,0.1); }
        
        .p-img-wrapper { position: relative; height: 280px; overflow: hidden; background: #fdfdfd; }
        .p-img-wrapper img { width: 100%; height: 100%; object-fit: contain; padding: 20px; transition: 0.5s; }
        .p-card:hover .p-img-wrapper img { transform: scale(1.1); }

        .p-details { padding: 20px; }
        .p-tag { font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase; }
        .p-name { font-size: 18px; font-weight: 700; margin: 5px 0; color: var(--dark); }
        .p-price { font-size: 1.2rem; font-weight: 600; color: #333; margin-bottom: 15px; }

        /* Form styling */
        .action-area { display: flex; flex-direction: column; gap: 10px; }
        .qty-input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; margin-bottom: 5px; }
        
        .btn { 
            padding: 12px; border: none; border-radius: 4px; 
            font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 13px; text-transform: uppercase;
        }
        .btn-buy { background: var(--dark); color: #fff; }
        .btn-buy:hover { background: var(--accent); }
        .btn-cart { background: #f0f0f0; color: var(--dark); border: 1px solid #ddd; }
        .btn-cart:hover { background: #e0e0e0; }

        .footer { background: var(--dark); color: #888; text-align: center; padding: 40px; margin-top: 60px; font-size: 13px; }

        @media (max-width: 768px) {
            .header { padding: 15px 5%; }
            .hero-content h1 { font-size: 2.2rem; }
            .nav ul { gap: 15px; }
        }
    </style>
</head>
<body>
<?php include "loader.php"; ?>
<header class="header">
    <div class="logo">
        <a href="index.php"><img src="image/logoo.png" alt="KickFit"></a>
    </div>
    <nav class="nav">
        <ul>
            <li><a href="index.php">Shop</a></li>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <li><a href="login.php">Login</a></li>
            <?php else: ?>
                <li>
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/dashboard.php"><i class="fa-solid fa-user-shield"></i> Admin Panel</a>
                    <?php else: ?>
                        <a href="dashboard.php"><i class="fa-solid fa-user"></i> My Account</a>
                    <?php endif; ?>
                </li>
                
                <li>
                    <a href="cart.php" class="cart-link">
                        <i class="fa-solid fa-cart-shopping"></i> Cart
                        <span class="cart-badge"><?= $cart_count ?></span>
                    </a>
                </li>
                
                <li>
                    <a href="logout.php" style="color: var(--accent);"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<section class="hero">
    <div class="hero-content">
        <h1>Unleash Your Pace</h1>
        <p>Premium footwear for those who never stop moving.</p>
        <a href="#shop" class="hero-btn">Explore Now</a>
    </div>
</section>

<div class="cat-strip">
    <a href="index.php" class="cat-item <?= !$cat_filter ? 'active' : '' ?>">All Kicks</a>
    <?php while($cat = $categories->fetch_assoc()): ?>
        <a href="index.php?category_name=<?= urlencode($cat['name']) ?>" 
           class="cat-item <?= ($cat_filter == $cat['name']) ? 'active' : '' ?>">
            <?= ucfirst($cat['name']) ?>
        </a>
    <?php endwhile; ?>
</div>

<main class="container" id="shop">
    <h2 class="grid-title">New Arrivals</h2>
    
    <div class="product-grid">
        <?php while($row = $products->fetch_assoc()): ?>
        <div class="p-card">
            <div class="p-img-wrapper">
                <img src="image/<?= $row['image'] ?>" alt="<?= $row['name'] ?>" loading="lazy">
            </div>
            <div class="p-details">
                <span class="p-tag"><?= $row['stock'] < 5 ? 'Only '.$row['stock'].' Left!' : 'In Stock' ?></span>
                <h3 class="p-name"><?= $row['name'] ?></h3>
                <div class="p-price">₹ <?= number_format($row['price'], 2) ?></div>

                <div class="action-area">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?= $row['stock'] ?>">
                            
                            <button formaction="addtocart.php" class="btn btn-cart" style="width: 100%; margin-bottom: 8px;">
                                <i class="fa-solid fa-plus"></i> Add to Cart
                            </button>
                            <button type="submit" name="buy_now" class="btn btn-buy" style="width: 100%;">
                                Buy Instantly
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-buy" style="text-decoration: none; text-align:center">Login to Order</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</main>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> KickFit Footwear Co. All Rights Reserved.</p>
</footer>

</body>
</html>