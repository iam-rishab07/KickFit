<?php
session_start();
include "db.php";

if(!isset($_SESSION['cart']) || count($_SESSION['cart'])==0){
    echo "<h2 style='text-align:center;margin-top:50px;'>Your Cart is Empty 🛒</h2>";
    exit;
}

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <style>
    body{font-family:Arial;background:#f5f5f5;padding:40px}
    table{width:80%;margin:auto;border-collapse:collapse;background:white;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
    th,td{padding:12px;text-align:center;border-bottom:1px solid #ddd}
    th{background:#e74c3c;color:white}
    .checkout{display:block;width:200px;margin:30px auto;padding:12px;background:#27ae60;color:white;text-align:center;text-decoration:none;border-radius:6px}
    </style>
</head>
<body>

    <h2 style="text-align:center;">My Cart</h2>

    <table>
        <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Total</th>
        </tr>

        <?php
        foreach($_SESSION['cart'] as $pid => $qty){
            $res = mysqli_query($conn,"SELECT * FROM products WHERE id='$pid'");
            $row = mysqli_fetch_assoc($res);
            $subtotal = $row['price'] * $qty;
            $total += $subtotal;
        ?>
        <tr>
        <td><?php echo $row['name'];?></td>
        <td>Rs. <?php echo $row['price'];?></td>
        <td><?php echo $qty;?></td>
        <td>Rs. <?php echo $subtotal;?></td>
        </tr>
        <?php } ?>

        <tr>
        <th colspan="3">Grand Total</th>
        <th>Rs. <?php echo $total;?></th>
        </tr>
    </table>

    <a href="checkout.php" class="checkout">Proceed to Checkout</a>

</body>
</html>
