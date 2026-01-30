<?php

session_start();
include "../db.php";
if(isset($_SESSION['user_id']))
    {
        if($_SESSION['user_role'] == "admin"){
            if(isset($_GET['product_id'])){
                $product_id = $_GET['product_id'];
                $sql = "delete from products where id = '$product_id'";
                $result = mysqli_query($conn,$sql);
                if(!$result){
                    echo "Error! : {$conn->error}";
                }else{
                    echo "Deleted Successfully! <a href='displayproduct.php'>Go Back</a>";
                }
            }
        }else{
            echo "Go for user Dashboard";
        }
        
    }else{
        header("Location: ../index.php");
    }

?>