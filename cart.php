<?php
session_start();
include "db.php";

/* ---------- SECURITY CHECK ---------- */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------- UPDATE QUANTITY LOGIC ---------- */
if (isset($_GET['action']) && isset($_GET['cart_id'])) {
    $c_id = intval($_GET['cart_id']);
    
    if ($_GET['action'] == 'add') {
        // Ensure we don't exceed stock when adding
        $conn->query("UPDATE cart c JOIN products p ON c.product_id = p.id SET c.quantity = c.quantity + 1 WHERE c.id = $c_id AND c.user_id = $user_id AND c.quantity < p.stock");
    } elseif ($_GET['action'] == 'sub') {
        $conn->query("UPDATE cart SET quantity = quantity - 1 WHERE id = $c_id AND user_id = $user_id AND quantity > 1");
    }
    header("Location: cart.php");
    exit();
}

/* ---------- FETCH CART ---------- */
$stmt = $conn->prepare("SELECT c.id as cart_id, p.id as prod_id, p.name, p.price, p.image, c.quantity, p.stock 
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$item_count = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Bag | KickFit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --dark: #0f1113;
            --gray: #f8fafc;
            --text-main: #1d1d1f;
            --muted: #86868b;
        }

        body { background: var(--gray); color: var(--text-main); font-family: 'Inter', sans-serif; margin: 0; }
        
        .nav-minimal { background: #fff; padding: 15px 8%; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 100; }
        .nav-minimal .logo { font-weight: 900; font-size: 24px; text-decoration: none; color: var(--dark); letter-spacing: -1px; }
        .nav-minimal .logo span { color: var(--primary); }

        .cart-wrapper { max-width: 1100px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 30px; }

        @media (max-width: 900px) { .cart-wrapper { grid-template-columns: 1fr; } }

        /* ITEM CARD */
        .cart-item {
            background: #fff; border-radius: 16px; padding: 20px; display: flex; 
            gap: 20px; margin-bottom: 15px; border: 1px solid #f0f0f0; transition: 0.3s;
        }
        .item-image { width: 110px; height: 110px; object-fit: cover; border-radius: 12px; background: #f9f9f9; }
        .item-details { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        
        .item-name { font-size: 17px; font-weight: 700; text-decoration: none; color: var(--dark); margin-bottom: 5px; display: block; }
        .item-price { color: var(--muted); font-size: 14px; }
        .item-subtotal { font-weight: 700; color: var(--dark); }

        /* QUANTITY BUTTONS */
        .qty-control { display: flex; align-items: center; background: #f1f1f1; border-radius: 8px; padding: 4px; width: fit-content; }
        .qty-btn { 
            width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; 
            background: #fff; border-radius: 6px; text-decoration: none; color: var(--dark); 
            font-weight: bold; font-size: 18px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .qty-btn:hover { background: var(--primary); color: #fff; }
        .qty-num { width: 40px; text-align: center; font-weight: 700; font-size: 14px; }

        /* SUMMARY - FIXED OVERFLOW */
        .summary-box { 
            background: var(--dark); 
            color: #fff; 
            padding: 30px; 
            border-radius: 20px; 
            position: sticky; 
            top: 100px; 
            box-sizing: border-box; /* Ensures padding doesn't push width out */
            width: 100%;
        }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 15px; color: #a1a1a1; }
        .total-row { border-top: 1px solid #333; margin-top: 20px; padding-top: 20px; font-weight: 700; font-size: 22px; color: #fff; }

        .btn-checkout {
            display: flex; 
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%; 
            padding: 18px 0; 
            background: var(--primary); 
            color: #fff; 
            text-align: center; 
            text-decoration: none; 
            border-radius: 12px;
            font-weight: 700; 
            margin-top: 25px; 
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(192, 57, 43, 0.3); }

        .btn-remove { color: #ff4757; text-decoration: none; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #a1a1a1;
            font-size: 13px;
            text-align: center;
            width: 100%;
            transition: 0.3s;
        }
        .back-btn:hover { color: #fff; }
    </style>
</head>
<body>

<nav class="nav-minimal">
    <a href="index.php" class="logo">KICK<span>FIT</span></a>
    <div style="font-size: 14px; font-weight: 600;"><i class="fa-solid fa-lock"></i> SECURE CHECKOUT</div>
</nav>

<div class="cart-wrapper">
    <div>
        <h2 style="margin-bottom: 25px;">Review Your Bag</h2>

        <?php if($item_count == 0): ?>
            <div style="text-align: center; padding: 50px; background: #fff; border-radius: 20px;">
                <i class="fa-solid fa-cart-shopping" style="font-size: 50px; color: #eee; margin-bottom: 20px;"></i>
                <h3 style="margin:0">Empty Bag</h3>
                <p style="color:var(--muted)">You haven't added any heat yet.</p>
                <a href="index.php" style="color:var(--primary); font-weight:bold; text-decoration:none;">Start Shopping &rarr;</a>
            </div>
        <?php else: ?>

            <?php while($row = $result->fetch_assoc()): 
                $subtotal = $row['price'] * $row['quantity'];
                $total += $subtotal;
            ?>
            <div class="cart-item">
                <img src="image/<?php echo $row['image']; ?>" class="item-image">
                <div class="item-details">
                    <div>
                        <div style="display: flex; justify-content: space-between;">
                            <a href="product.php?id=<?php echo $row['prod_id']; ?>" class="item-name"><?php echo $row['name']; ?></a>
                            <span class="item-subtotal">₹ <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <span class="item-price">₹ <?php echo number_format($row['price'], 2); ?> each</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                        <div class="qty-control">
                            <a href="cart.php?action=sub&cart_id=<?php echo $row['cart_id']; ?>" class="qty-btn">-</a>
                            <div class="qty-num"><?php echo $row['quantity']; ?></div>
                            <a href="cart.php?action=add&cart_id=<?php echo $row['cart_id']; ?>" class="qty-btn" 
                               style="<?php echo ($row['quantity'] >= $row['stock']) ? 'pointer-events:none; opacity:0.5;' : ''; ?>">+</a>
                        </div>
                        <a href="removecart.php?id=<?php echo $row['cart_id']; ?>" class="btn-remove" onclick="return confirm('Remove this item?')">
                            <i class="fa-solid fa-trash"></i> Remove
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php if($item_count > 0): ?>
    <div>
        <div class="summary-box">
            <h2 style="margin-top: 0;">Summary</h2>
            <div class="summary-row">
                <span>Items Subtotal</span>
                <span>₹ <?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping & Handling</span>
                <span style="color: #4cc9f0;">FREE</span>
            </div>
            <div class="total-row">
                <span>Total Amount</span>
                <span>₹ <?php echo number_format($total, 2); ?></span>
            </div>
            
            <a href="checkout.php" class="btn-checkout">
                Checkout Now <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a href="index.php" class="back-btn">
                <i class="fa-solid fa-chevron-left" style="font-size: 10px;"></i> Continue Shopping
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>