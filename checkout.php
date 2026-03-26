<?php
session_start();
include "db.php";

/* ---------- SECURITY ---------- */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------- FETCH ITEMS (Prepared) ---------- */
$stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price, p.stock 
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_cart = $stmt->get_result();

$total_amount = 0;
$cart_items = [];

while($row = $result_cart->fetch_assoc()){
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total_amount += $row['subtotal'];
    $cart_items[] = $row;
}

/* ---------- ORDER PROCESSING (Transaction-Safe) ---------- */
if(isset($_POST['place_order']) && !empty($cart_items)){
    
    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Create Order
        $stmt_ord = $conn->prepare("INSERT INTO orders(user_id, total_amount, order_date) VALUES(?, ?, NOW())");
        $stmt_ord->bind_param("id", $user_id, $total_amount);
        $stmt_ord->execute();
        $order_id = $conn->insert_id;

        // 2. Process Items
        $stmt_item = $conn->prepare("INSERT INTO order_items(order_id, product_id, quantity, price) VALUES(?, ?, ?, ?)");
        $stmt_update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach($cart_items as $item){
            $stmt_item->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt_item->execute();

            $stmt_update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt_update_stock->execute();
        }

        // 3. Payment Entry
        $method = "COD";
        $stmt_pay = $conn->prepare("INSERT INTO payments(order_id, user_id, total_amount, payment_method) VALUES(?, ?, ?, ?)");
        $stmt_pay->bind_param("iids", $order_id, $user_id, $total_amount, $method);
        $stmt_pay->execute();

        // 4. Clear Cart
        $stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_clear->bind_param("i", $user_id);
        $stmt_clear->execute();

        // Commit everything
        mysqli_commit($conn);

        echo "<script>alert('🔥 Boom! Order Placed.'); window.location='index.php';</script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Something went wrong. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout | KickFit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --dark: #0f1113;
            --bg: #f4f6f9;
            --text: #1d1d1f;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 40px 20px; }
        .checkout-container { max-width: 600px; margin: 0 auto; }
        
        .header-logo { text-align: center; margin-bottom: 30px; font-weight: 900; font-size: 28px; text-decoration: none; color: var(--dark); }
        .header-logo span { color: var(--primary); }

        .card { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card h2 { font-size: 18px; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

        .item-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; }
        .item-row .qty { color: #888; margin-right: 5px; }

        .price-breakdown { margin-top: 20px; padding-top: 15px; border-top: 2px dashed #eee; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 15px; }
        .total-row { font-size: 20px; font-weight: 800; color: var(--primary); margin-top: 10px; }

        .payment-option {
            display: flex; align-items: center; gap: 15px; padding: 15px;
            border: 2px solid var(--primary); border-radius: 10px; background: #fff5f5;
            cursor: pointer;
        }

        .place-order-btn {
            width: 100%; padding: 18px; background: var(--dark); color: #fff;
            border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        .place-order-btn:hover { background: var(--primary); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(192, 57, 43, 0.3); }
        
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #888; font-size: 13px; }
    </style>
</head>
<body>

<div class="checkout-container">
    <div style="text-align: center;"><a href="index.php" class="header-logo">KICK<span>FIT</span></a></div>

    <?php if(empty($cart_items)): ?>
        <div class="card" style="text-align:center; padding: 60px 20px;">
            <i class="fa-solid fa-box-open" style="font-size: 40px; color: #ddd; margin-bottom: 20px;"></i>
            <h3>No items to checkout.</h3>
            <a href="index.php" style="color:var(--primary); font-weight:700; text-decoration:none;">Go back to shop</a>
        </div>
    <?php else: ?>

        <div class="card">
            <h2><i class="fa-solid fa-receipt"></i> Order Summary</h2>
            <?php foreach($cart_items as $item): ?>
                <div class="item-row">
                    <span><span class="qty"><?php echo $item['quantity']; ?>x</span> <?php echo $item['name']; ?></span>
                    <span>₹<?php echo number_format($item['subtotal'], 2); ?></span>
                </div>
            <?php endforeach; ?>

            <div class="price-breakdown">
                <div class="price-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($total_amount, 2); ?></span>
                </div>
                <div class="price-row">
                    <span>Shipping</span>
                    <span style="color: #27ae60;">FREE</span>
                </div>
                <div class="price-row total-row">
                    <span>Total</span>
                    <span>₹<?php echo number_format($total_amount, 2); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2><i class="fa-solid fa-credit-card"></i> Payment Method</h2>
            <div class="payment-option">
                <i class="fa-solid fa-truck-ramp-box" style="font-size: 24px; color: var(--primary);"></i>
                <div>
                    <strong style="display:block">Cash on Delivery (COD)</strong>
                    <small style="color: #666;">Pay with cash when your kicks arrive.</small>
                </div>
                <i class="fa-solid fa-circle-check" style="margin-left:auto; color: var(--primary);"></i>
            </div>
        </div>

        <form method="post">
            <button name="place_order" class="place-order-btn">Confirm My Order</button>
        </form>

        <a href="cart.php" class="back-link"><i class="fa-solid fa-chevron-left"></i> Return to Cart</a>

    <?php endif; ?>
</div>

</body>
</html>