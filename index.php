<?php
    session_start();
    include "db.php";

    /* PRODUCT FILTER */
    if(isset($_GET['category_name'])) {
        $category_name = $_GET['category_name'];
        $sql_product_category = "SELECT * FROM products WHERE category_name='$category_name' AND stock>0";
    } else {
        $sql_product_category = "SELECT * FROM products WHERE stock>0";
    }
    $result_product_category = mysqli_query($conn,$sql_product_category);

    /* CATEGORIES */
    $sql_category = "SELECT * FROM categories";
    $result_category = mysqli_query($conn,$sql_category);
?>

<!DOCTYPE html>
<html>
<head>
<title>KickFit Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f5f5f5;display:flex;flex-direction:column;min-height:100vh}

/* HEADER */
.header{
position:fixed;top:0;width:100%;
background:#e74c3c;color:white;
display:flex;justify-content:space-between;align-items:center;
padding:15px 40px;box-shadow:0 4px 10px rgba(0,0,0,0.1);z-index:1000;
}
.header a{color:white;text-decoration:none;font-weight:bold}
.header ul{display:flex;list-style:none}
.header li{margin-left:20px}

/* CATEGORY BAR */
.categories{
background:white;padding:6px 14px;border-radius:25px;
box-shadow:0 2px 6px rgba(0,0,0,0.1);display:flex;gap:10px;
}
.categories span{
    color: #333;
}
.categories a{color:#333;text-decoration:none;font-weight:bold;font-size:14px}
.categories a:hover{color:#e74c3c}

/* MAIN */
.main{flex:1;margin-top:110px;display:flex;flex-wrap:wrap;justify-content:center;gap:25px;padding:30px}

/* PRODUCT CARD */
.product{
background:white;width:260px;padding:20px;border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);text-align:center;transition:0.3s;
}
.product:hover{transform:translateY(-6px)}
.product img{width:100%;height:180px;object-fit:cover;border-radius:6px}
.product h3{margin:10px 0}
.product p{font-size:14px;color:#555;margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* QTY */
.qty-box{display:flex;justify-content:center;align-items:center;gap:8px;margin-bottom:10px}
.qty-box input{width:60px;padding:6px;text-align:center;border-radius:6px;border:1px solid #ccc}

/* BUTTON */
.buy-btn{
width:100%;padding:10px;border:none;border-radius:6px;
background:linear-gradient(135deg,#e74c3c,#ff6b5a);
color:white;font-weight:bold;cursor:pointer;transition:0.3s;text-decoration:none;display:block
}
.buy-btn:hover{background:#333;transform:scale(1.03)}

/* FOOTER */
.footer{background:#333;color:white;text-align:center;padding:15px}
</style>
</head>

<body>

    <header class="header">
        <h1><a href="index.php">KickFit</a></h1>

        <div class="categories">
            <span>Categories:</span>
            <?php while($row_category=mysqli_fetch_assoc($result_category)){ ?>
            <a href="index.php?category_name=<?php echo $row_category['name'];?>"><?php echo ucfirst($row_category['name']);?></a>
            <?php } ?>
        </div>

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
    </header>

    <main class="main">
        <?php while($row=mysqli_fetch_assoc($result_product_category)){ ?>
            <div class="product">
                <img src="image/<?php echo $row['image'];?>">
                <h3><?php echo $row['name'];?></h3>
                <p><?php echo $row['description'];?></p>
                <p style="color:green;font-weight:bold;">Stock: <?php echo $row['stock'];?></p>
                <p style="font-weight:bold;">Rs. <?php echo $row['price'];?></p>

                <?php if(isset($_SESSION['user_id'])){ ?>
                <form action="singleorder.php" method="get">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'];?>">
                <input type="hidden" name="product_id" value="<?php echo $row['id'];?>">
                <input type="hidden" name="product_price" value="<?php echo $row['price'];?>">

                <div class="qty-box">
                    <label>Qty</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['stock'];?>">
                </div>

                <button type="submit" class="buy-btn">Buy Now</button>
                </form>
                <?php } else { ?>
                <a href="login.php" class="buy-btn">Buy Now</a>
                <?php } ?>
            </div>
        <?php } ?>
    </main>

    <footer class="footer">Copyright ©Rishi Enterprises</footer>
</body>
</html>
