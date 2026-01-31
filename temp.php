<?php
session_start();
include "db.php";
if(isset($_GET['category_name']))
    {
        $category_name = $_GET['category_name'];
        $sql_product_category = "select * from products where category_name = '$category_name'";
        $result_product_category = mysqli_query($conn,$sql_product_category);
    }else{
        $sql_product_category = "select * from products";
        $result_product_category = mysqli_query($conn,$sql_product_category);
    }
$sql_category = "select * from categories";
$result_category = mysqli_query($conn,$sql_category);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <!-- <style>
        *{
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .header{
            position: fixed;
            top: 0;
            width: 100%;
            background-color: gray;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 30px;
        }
        .header ul li{
            list-style: none;
        }
        
        .header a{
            text-decoration: none;
            color: white;
        }
        .header li{
            display: inline-block;
            margin-right: 50px;
        }
        .footer{
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            background-color: grey;
            position: fixed;
            bottom: 0;
            padding: 10px;
            width: 100%;
        }
        .footer p{
            text-align: center;
        }
        @media(max-width: 400px)
        {
            .header{
                display: flex;
                flex-direction: column;
            }
            .footer{
                display: flex;
                flex-direction: column;
            }
        }
        .main{
            margin-top: 100px;
            justify-content: center;
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 90px;
        }
        .product {
            border: none;
            max-width: 300px;
            padding: 30px;
            text-align: center;
            border: 2px solid black;
            margin: 10px;
        }
        .product img{
            width:150px;
        }
        .productpricce{
            opacity: 70%;
        }
        
        .product a{
            display: block;
            text-decoration: none;
            color: black;
            background-color: greenyellow;
            padding: 10px;
            margin-top: 10px;
            width: 100%;
        }
        /* .main a{
            text-decoration: none;
            color: white;
            background-color: greenyellow;
            padding: 5px;
            margin: 2px;
        } */
    </style> -->

<style>
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

body{
    background-color: #f5f5f5;
}

/* HEADER */
.header{
    position: fixed;
    top: 0;
    width: 100%;
    background-color: #e74c3c;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 40px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.header h1{
    color: white;
    font-size: 26px;
    letter-spacing: 1px;
}

.header ul{
    display: flex;
    align-items: center;
}

.header ul li{
    list-style: none;
    margin-left: 25px;
}

.header a{
    text-decoration: none;
    color: white;
    font-weight: bold;
    transition: 0.3s;
}

.header a:hover{
    opacity: 0.8;
}

/* HERO SECTION (optional if used) */
.hero{
    margin-top: 90px;
    padding: 60px 30px;
    text-align: center;
    background: white;
}

.hero h2{
    font-size: 36px;
    margin-bottom: 10px;
}

.hero p{
    font-size: 18px;
    color: #555;
}

.hero button{
    margin-top: 20px;
    padding: 12px 25px;
    font-size: 16px;
    background-color: #e74c3c;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

/* MAIN AREA */
.main{
    margin-top: 30px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    padding: 30px;
    gap: 25px;
}

/* PRODUCT CARD */
.product{
    background: white;
    width: 260px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 12px rgba(0,0,0,0.08);
    text-align: center;
    transition: 0.3s;
}

.product:hover{
    transform: translateY(-6px);
}

.product img{
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
}

.product h3{
    margin: 12px 0;
    color: #222;
}

.productprice{
    color: #555;
    margin-bottom: 10px;
    font-weight: bold;
}

/* BUTTON */
.product a{
    display: block;
    text-decoration: none;
    background-color: #e74c3c;
    color: white;
    padding: 10px;
    border-radius: 6px;
    transition: 0.3s;
}

.product a:hover{
    background-color: #333;
}

/* FOOTER */
.footer{
    background-color: #333;
    color: white;
    text-align: center;
    padding: 15px;
    margin-top: 40px;
}

/* RESPONSIVE */
@media(max-width: 600px){
    .header{
        flex-direction: column;
        gap: 10px;
    }
    .header ul{
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>


</head>
<body>
    <header class="header">
        
        <a href="index.php"><img src="" alt="">KickFit</a>
        
        <!-- Categories -->
        <!-- <div class="categories">
            <span>Categories :</span>
            <a href="index.php?category_name=men">Men</a>
            <a href="index.php?category_name=women">Women</a>
            <a href="index.php?category_name=kids">Kids</a>
        </div> -->

        <ul>
            <?php while($row_category = mysqli_fetch_assoc($result_category)){?>
            <li><a href="index.php?<?php echo $row_category['name']?>"><?php echo $row_category['name']?></a></li>
            <?php }?>
        </ul>

        <nav>
            <ul>
                <?php if(!isset($_SESSION['user_id'])){?>
                <li><a href="login.php">login</a></li>
                <li><a href="register.php">signup</a></li>
                <?php }?>
                <li><a href="index.php">Shop</a></li>

                <?php if(isset($_SESSION['user_id'])) {?>
                <li><a href="admin/dashboard.php">dashboard</a></li>
                <?php }?>
            </ul>
        </nav>
    </header>
    <main class="main">
        <?php while($row = mysqli_fetch_assoc($result_product_category)){
            ?>
            <div class="product"> 
                <img src="image/<?php echo $row['image']; ?>" alt="productimg">
                <h3><?php echo $row['name']; ?></h3>
                <p><?php echo $row['description']; ?></p>
                <p style="color:green; font-weight:bold;">
                <?php echo "Pcs Left:".$row['stock'] ?>
                </p>
                <p class="productprice">
                <?php echo "Rs.".$row['price']; ?>
                </p>
                <a href="#">Add to Cart</a>
            </div>
        <?php }?>

      

    </main>
    <footer class="footer">
        <p>Copyright@: Rishi Enterprises</p>
    </footer>
</body>
</html>