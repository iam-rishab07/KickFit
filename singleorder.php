<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if(isset($_GET['user_id'],$_GET['product_id'],$_GET['product_price'],$_GET['quantity'])){

    $user_id = $_GET['user_id'];
    $product_id = $_GET['product_id'];
    $product_price = $_GET['product_price'];
    $quantity = $_GET['quantity'];

    // CHECK STOCK
    $check_stock = mysqli_query($conn,"SELECT stock FROM products WHERE id='$product_id'");
    $row_stock = mysqli_fetch_assoc($check_stock);

    if($row_stock['stock'] < $quantity){
        echo "Not enough stock! <a href='index.php'>Go Back</a>";
        exit();
    }

    $total_amount = $product_price * $quantity;

    // INSERT ORDER
    $sql = "INSERT INTO single_order(user_id,product_id,product_quantity,total_amount)
            VALUES('$user_id','$product_id','$quantity','$total_amount')";
    $result = mysqli_query($conn,$sql);

    if(!$result){
        echo "Error: {$conn->error}";
        exit();
    }

    $order_id = mysqli_insert_id($conn);

    // INSERT PAYMENT
    $payment_method="cashon";
    mysqli_query($conn,"INSERT INTO payments(order_id,user_id,total_amount,payment_method)
                        VALUES('$order_id','$user_id','$total_amount','$payment_method')");

    // UPDATE STOCK
    mysqli_query($conn,"UPDATE products SET stock = stock - $quantity WHERE id='$product_id'");

    echo "<h2>Order Successful 🎉</h2>
          <p>Total Amount: Rs.$total_amount</p>
          <a href='index.php'>Buy More</a>";

}else{
    header("Location: index.php");
}
?>
