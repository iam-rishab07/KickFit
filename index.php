<?php
session_start();
include "db.php";

/* PRODUCT FILTER */
if(isset($_GET['category_name'])) {
    $category_name = mysqli_real_escape_string($conn,$_GET['category_name']);
    $sql_product_category = "SELECT id,name,description,price,stock,image 
                             FROM products 
                             WHERE category_name='$category_name' AND stock>0";
} else {
    $sql_product_category = "SELECT id,name,description,price,stock,image 
                             FROM products 
                             WHERE stock>0";
}
$result_product_category = mysqli_query($conn,$sql_product_category);

/* CATEGORIES */
$result_category = mysqli_query($conn,"SELECT name FROM categories");

/* CART COUNT */
$cart_count = 0;
if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $count_res = mysqli_query($conn,"SELECT SUM(quantity) as total FROM cart WHERE user_id='$uid'");
    $cart_data = mysqli_fetch_assoc($count_res);
    $cart_count = $cart_data['total'] ? $cart_data['total'] : 0;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>KickFit — Step Into Style</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,Helvetica,sans-serif}
body{background:#f2f3f7;color:#111}

/* HEADER */
.header{
position:fixed;top:0;width:100%;z-index:1000;
display:flex;justify-content:space-between;align-items:center;
padding:12px 50px;background:#fff;
box-shadow:0 1px 6px rgba(0,0,0,.08);
}

/* LOGO IMAGE */
.logo img{
height:45px;
width:auto;
display:block;
}

.nav ul{display:flex;list-style:none;align-items:center}
.nav li{margin-left:24px}
.nav a{text-decoration:none;color:#333;font-weight:600}

/* CART BADGE */
.cart{
position:relative;font-size:20px;
}
.cart-count{
position:absolute;top:-8px;right:-10px;
background:#e74c3c;color:#fff;
font-size:12px;border-radius:50%;
padding:3px 7px;
}

/* HERO */
.hero{
margin-top:78px;height:400px;
background:linear-gradient(to right,#111 45%,transparent),
url('image/puma.jpg') center/cover no-repeat;
display:flex;align-items:center;padding-left:70px;color:#fff;
}
.hero h1{font-size:46px}
.hero p{opacity:.85;margin:10px 0 18px}
.hero-btn{background:#e74c3c;color:#fff;padding:12px 26px;border-radius:30px;text-decoration:none;font-weight:bold}

/* CATEGORY */
.category-strip{
background:#fff;padding:16px 40px;
display:flex;gap:16px;overflow-x:auto;border-bottom:1px solid #eee;
}
.category-strip a{
padding:9px 18px;background:#f4f4f4;border-radius:20px;text-decoration:none;color:#333;font-weight:bold;white-space:nowrap;
}

/* PRODUCTS */
.section-title{margin:40px 60px 10px;font-size:26px}
.products{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(230px,1fr));
gap:26px;padding:20px 60px 60px;
}
.product{
background:#fff;border-radius:14px;overflow:hidden;
display:flex;flex-direction:column;
transition:transform .25s ease;
}
.product:hover{transform:translateY(-4px)}

.product img{
width:100%;height:200px;object-fit:cover;display:block;
}

.product-body{padding:14px;display:flex;flex-direction:column;flex:1}
.product-body h3{font-size:17px;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.desc{font-size:14px;color:#666;height:36px;overflow:hidden;margin-bottom:6px}
.price{font-weight:bold;margin-bottom:8px}

/* QTY */
.qty-box{display:flex;gap:8px;margin-bottom:8px}
.qty-box input{width:55px;padding:6px;border-radius:6px;border:1px solid #ccc;text-align:center}

/* BUTTONS */
.buy-btn,.cart-btn{
padding:9px;border-radius:8px;text-align:center;font-weight:bold;border:none;cursor:pointer;width:100%;margin-bottom:6px
}
.buy-btn{background:#111;color:#fff}
.buy-btn:hover{background:#e74c3c}
.cart-btn{background:#e74c3c;color:#fff}
.cart-btn:hover{background:#111}

/* FOOTER */
.footer{background:#111;color:#aaa;text-align:center;padding:22px;margin-top:40px;font-size:14px}
</style>
</head>

<body>

<header class="header">
    <div class="logo">
        <a href="index.php">
            <img src="image/logoo.png" alt="KickFit Logo">
        </a>
    </div>
    <nav class="nav">
        <ul>
            <li><a href="index.php">Shop</a></li>
            <?php if(!isset($_SESSION['user_id'])){ ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Signup</a></li>
            <?php } else { ?>
                <li><a href="admin/dashboard.php">Dashboard</a></li>
                <li class="cart">
                    <a href="cart.php">🛒</a>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </li>
            <?php } ?>
        </ul>
    </nav>
</header>

<!-- REST OF YOUR CODE REMAINS SAME -->

<section class="hero">
    <div>
        <h1>Move Different. Walk Bold.</h1>
        <p>Performance meets street style.</p>
        <a href="#shop" class="hero-btn">Shop Collection</a>
    </div>
</section>

<div class="category-strip">
<?php while($cat=mysqli_fetch_assoc($result_category)){ ?>
    <a href="index.php?category_name=<?php echo $cat['name'];?>">
        <?php echo ucfirst($cat['name']);?>
    </a>
<?php } ?>
</div>

<h2 class="section-title" id="shop">Popular Right Now</h2>

<section class="products">
<?php while($row=mysqli_fetch_assoc($result_product_category)){ ?>
<div class="product">
    <img src="image/<?php echo $row['image'];?>" loading="lazy">
    <div class="product-body">
        <h3><?php echo $row['name'];?></h3>
        <div class="desc"><?php echo $row['description'];?></div>
        <div class="price">₹ <?php echo $row['price'];?></div>

        <?php if(isset($_SESSION['user_id'])){ ?>
        <form action="addtocart.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $row['id'];?>">
            <div class="qty-box">
                <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['stock'];?>">
            </div>
            <button class="cart-btn">Add to Cart</button>
        </form>

        <form action="singleorder.php" method="get">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'];?>">
            <input type="hidden" name="product_id" value="<?php echo $row['id'];?>">
            <input type="hidden" name="product_price" value="<?php echo $row['price'];?>">
            <button class="buy-btn">Buy Now</button>
        </form>
        <?php } else { ?>
            <a href="login.php" class="buy-btn">Login to Buy</a>
        <?php } ?>
    </div>
</div>
<?php } ?>
</section>

<footer class="footer">© KickFit — Fashion Forward Footwear</footer>

</body>
</html>
