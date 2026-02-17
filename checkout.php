<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* GET CART ITEMS */
$sql_cart = "SELECT c.product_id, c.quantity, p.name, p.price, p.stock
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.user_id = '$user_id'";
$result_cart = mysqli_query($conn, $sql_cart);

$total_amount = 0;
$cart_items = [];

while($row = mysqli_fetch_assoc($result_cart)){
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total_amount += $row['subtotal'];
    $cart_items[] = $row;
}

/* PLACE ORDER */
if(isset($_POST['place_order'])){

    // 1️⃣ Create Order
    $sql_order = "INSERT INTO orders(user_id, total_amount, order_date)
                  VALUES('$user_id', '$total_amount', NOW())";
    mysqli_query($conn, $sql_order);
    $order_id = mysqli_insert_id($conn);

    // 2️⃣ Insert Order Items
    foreach($cart_items as $item){
        $pid = $item['product_id'];
        $qty = $item['quantity'];
        $price = $item['price'];

        mysqli_query($conn,"INSERT INTO order_items(order_id,product_id,quantity,price)
                            VALUES('$order_id','$pid','$qty','$price')");

        // 3️⃣ Reduce Stock
        mysqli_query($conn,"UPDATE products SET stock = stock - $qty WHERE id = '$pid'");
    }

    // 4️⃣ Payment (Cash on Delivery)
    mysqli_query($conn,"INSERT INTO payments(order_id,user_id,total_amount,payment_method)
                        VALUES('$order_id','$user_id','$total_amount','COD')");

    // 5️⃣ Clear Cart
    mysqli_query($conn,"DELETE FROM cart WHERE user_id='$user_id'");

    echo "<script>alert('Order Placed Successfully!'); window.location='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout | KickFit</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{font-family:Arial;background:#f4f6f9;margin:0}
.container{max-width:900px;margin:80px auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 4px 18px rgba(0,0,0,.08)}
h2{margin-bottom:20px}
.item{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #eee}
.total{font-size:20px;font-weight:bold;text-align:right;margin-top:20px}
button{
margin-top:20px;width:100%;padding:12px;
background:#e74c3c;color:#fff;border:none;
border-radius:8px;font-size:16px;font-weight:bold;cursor:pointer
}
button:hover{background:#111}
.empty{text-align:center;padding:40px;color:#777}
</style>
</head>

<body>

<div class="container">
<h2>Checkout 🧾</h2>

<?php if(empty($cart_items)){ ?>
    <div class="empty">Your cart is empty.</div>
<?php } else { ?>

    <?php foreach($cart_items as $item){ ?>
        <div class="item">
            <div>
                <strong><?php echo $item['name']; ?></strong><br>
                Qty: <?php echo $item['quantity']; ?>
            </div>
            <div>₹ <?php echo $item['subtotal']; ?></div>
        </div>
    <?php } ?>

    <div class="total">Total: ₹ <?php echo $total_amount; ?></div>

    <form method="post">
        <button name="place_order">Place Order (Cash on Delivery)</button>
    </form>

<?php } ?>
</div>

</body>
</html>
