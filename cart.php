<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT c.id as cart_id, p.name, p.price, p.image, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = '$user_id'";
$result = mysqli_query($conn,$sql);

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>My Cart | KickFit</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    margin:0;
    font-family:Arial, Helvetica, sans-serif;
    background:#f4f6f9;
}

/* HEADER */
.header{
    background:#111;
    color:#fff;
    padding:16px 40px;
    font-size:22px;
    font-weight:bold;
}

/* CONTAINER */
.cart-container{
    max-width:1000px;
    margin:40px auto;
    padding:20px;
}

/* CART CARD */
.cart-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:#fff;
    padding:18px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
    margin-bottom:18px;
    transition:0.2s;
}
.cart-item:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 18px rgba(0,0,0,.12);
}

.cart-left{
    display:flex;
    align-items:center;
    gap:18px;
}

.cart-left img{
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:10px;
}

.cart-info h4{
    margin:0 0 6px;
    font-size:18px;
}

.cart-info p{
    margin:4px 0;
    color:#666;
    font-size:14px;
}

/* REMOVE BTN */
.remove-btn{
    background:#e74c3c;
    color:#fff;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
    font-weight:bold;
    transition:0.3s;
}
.remove-btn:hover{background:#111}

/* TOTAL BOX */
.cart-summary{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
    margin-top:30px;
    text-align:right;
}

.cart-summary h3{
    margin-bottom:15px;
    font-size:22px;
}

/* CHECKOUT BUTTON */
.checkout-btn{
    display:inline-block;
    background:#111;
    color:#fff;
    padding:12px 24px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}
.checkout-btn:hover{background:#e74c3c}

/* EMPTY CART */
.empty{
    text-align:center;
    padding:60px 0;
    color:#777;
    font-size:18px;
}
</style>
</head>

<body>

<div class="header">🛒 My Cart</div>

<div class="cart-container">

<?php if(mysqli_num_rows($result) == 0){ ?>
    <div class="empty">Your cart is empty 😢<br><br><a href="index.php">Continue Shopping</a></div>
<?php } else { ?>

<?php while($row=mysqli_fetch_assoc($result)){ 
    $subtotal = $row['price'] * $row['quantity'];
    $total += $subtotal;
?>

<div class="cart-item">
    <div class="cart-left">
        <img src="image/<?php echo $row['image']; ?>">
        <div class="cart-info">
            <h4><?php echo $row['name']; ?></h4>
            <p>Price: ₹ <?php echo $row['price']; ?></p>
            <p>Quantity: <?php echo $row['quantity']; ?></p>
            <p><strong>Subtotal: ₹ <?php echo $subtotal; ?></strong></p>
        </div>
    </div>

    <a class="remove-btn" href="removecart.php?id=<?php echo $row['cart_id']; ?>">Remove</a>
</div>

<?php } ?>

<div class="cart-summary">
    <h3>Total: ₹ <?php echo $total; ?></h3>
    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
</div>

<?php } ?>

</div>

</body>
</html>
