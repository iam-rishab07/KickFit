<?php
session_start();
include "db.php";
if(isset($_GET['category_name']))
    {
        $category_name = $_GET['category_name'];
        $sql_product_category = "select * from products where category_name = '$category_name' and stock >0";
        $result_product_category = mysqli_query($conn,$sql_product_category);
    }else{
        $sql_product_category = "select * from products where stock >0";
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

/* PAGE LAYOUT (Sticky Footer Fix) */
body{
    background-color: #f5f5f5;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
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
    padding: 16px 40px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.header a{
    text-decoration: none;
    color: white;
    font-weight: bold;
    transition: 0.3s;
}

.header a:hover{
    opacity: 0.85;
}

.header ul{
    display: flex;
    align-items: center;
}

.header ul li{
    list-style: none;
    margin-left: 25px;
}

/* CATEGORIES BOX */
.categories{
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    padding: 6px 14px;
    border-radius: 25px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.categories span{
    color: #e74c3c;
    font-weight: bold;
    font-size: 14px;
}

.categories a{
    text-decoration: none;
    color: #333;
    font-weight: bold;
    font-size: 14px;
    padding: 4px 10px;
    border-radius: 15px;
    transition: 0.3s;
}

.categories a:hover{
    background-color: #e74c3c;
    color: white;
}

/* HERO (optional) */
.hero{
    margin-top: 100px;
    padding: 60px 30px;
    text-align: center;
    background: white;
}

/* MAIN AREA */
.main{
    flex: 1; /* pushes footer down */
    margin-top: 100px; /* space for fixed header */
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
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
}

/* RESPONSIVE */
@media(max-width: 600px){
    .header{
        flex-direction: column;
        gap: 10px;
        padding: 15px;
    }

    .header ul{
        flex-wrap: wrap;
        justify-content: center;
    }

    .categories{
        flex-wrap: wrap;
        justify-content: center;
    }
}

.product .desc{
    font-size: 14px;
    color: #555;
    margin-bottom: 6px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
/* DESCRIPTION — FORCE ONE LINE */
.product p{
    font-size: 14px;
    color: #555;
    margin-bottom: 6px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


</style>

</head>
<body>
    <header class="header">

    <!-- Logo -->
    <h1><a href="index.php" style="color:white;text-decoration:none;">KickFit</a></h1>

    <!-- Categories Box -->
    <div class="categories">
        <span>Categories :</span>
        <?php while($row_category = mysqli_fetch_assoc($result_category)){ ?>
            <a href="index.php?category_name=<?php echo $row_category['name']; ?>">
                <?php echo ucfirst($row_category['name']); ?>
            </a>
        <?php } ?>
    </div>

    <!-- Navigation -->
    <nav>
        <ul>
            <?php if(!isset($_SESSION['user_id'])){ ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Signup</a></li>
            <?php } ?>

            <li><a href="index.php">Shop</a></li>

            <?php if(isset($_SESSION['user_id'])){ ?>
                <li><a href="admin/dashboard.php">Dashboard</a></li>
            <?php } ?>
        </ul>
    </nav>

</header>

    <main class="main">
        <?php while($row_product_category = mysqli_fetch_assoc($result_product_category)){
            ?>
            <div class="product"> 
                <img src="image/<?php echo $row_product_category['image']; ?>" alt="productimg">
                <h3><?php echo $row_product_category['name']; ?></h3>
                <p class="desc"><?php echo $row_product_category['description']; ?></p>
                <p style="color:green; font-weight:bold;">
                <?php echo "Pcs Left:".$row_product_category['stock'] ?>
                </p>
                <p class="productprice">
                <?php echo "Rs.".$row_product_category['price']; ?>
                </p>
                <?php if(isset($_SESSION['user_id'])){?>
                <a href="singleorder.php?user_id=<?php echo $_SESSION['user_id']?>&product_id=<?php echo $row_product_category['id']?>&product_price=<?php echo $row_product_category['price'];?>">Buy Now</a>
                <?php }?>
                <?php if(!isset($_SESSION['user_id'])){?>
                <a href="login.php">Buy Now</a>
                <?php }?>
            </div>
        <?php }?>

      

    </main>
    <footer class="footer">
        <p>Copyright@: Rishi Enterprises</p>
    </footer>
</body>
</html>