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
<title>KickFit — Step Into Style</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial, Helvetica, sans-serif}
body{background:#f2f3f7;color:#111}

/* ============ HEADER ============ */
.header{
position:fixed;top:0;width:100%;z-index:1000;
display:flex;justify-content:space-between;align-items:center;
padding:18px 60px;
background:white;
box-shadow:0 2px 12px rgba(0,0,0,0.06);
}

.logo{
font-size:26px;
font-weight:900;
letter-spacing:1px;
color:#e74c3c;
}

.nav ul{display:flex;list-style:none;align-items:center}
.nav li{margin-left:28px}
.nav a{text-decoration:none;color:#333;font-weight:600}
.nav a:hover{color:#e74c3c}

/* ============ HERO ============ */
.hero{
margin-top:80px;
height:420px;
background:linear-gradient(to right,#111 40%,transparent),
url('image/puma.jpg');
background-size:cover;
background-position:center;
display:flex;
align-items:center;
padding-left:80px;
color:white;
}

.hero-text h1{
font-size:48px;
margin-bottom:12px;
}

.hero-text p{
font-size:18px;
opacity:0.85;
margin-bottom:20px;
}

.hero-btn{
display:inline-block;
padding:12px 26px;
background:#e74c3c;
color:white;
border-radius:30px;
text-decoration:none;
font-weight:bold;
transition:0.3s;
}
.hero-btn:hover{background:white;color:#e74c3c}

/* ============ CATEGORY STRIP ============ */
.category-strip{
background:white;
padding:18px 40px;
display:flex;
gap:18px;
overflow-x:auto;
border-bottom:1px solid #eee;
}

.category-strip a{
flex:0 0 auto;
padding:10px 18px;
background:#f4f4f4;
border-radius:20px;
text-decoration:none;
color:#333;
font-weight:bold;
transition:0.3s;
}
.category-strip a:hover{
background:#e74c3c;
color:white;
}

/* ============ SECTION TITLE ============ */
.section-title{
font-size:28px;
font-weight:bold;
margin:40px 60px 10px;
}

/* ============ PRODUCTS ============ */
.products{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap:30px;
    padding:20px 60px 60px;
}

.product{
    background:white;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 8px 24px rgba(0,0,0,0.08);
    transition:0.3s;
    display:flex;
    flex-direction:column;
}

.product:hover{
transform:translateY(-8px);
box-shadow:0 12px 30px rgba(0,0,0,0.15);
}

.product img{
width:100%;
height:200px;
object-fit:cover;
}

.product-body{
padding:15px;
display:flex;
flex-direction:column;
flex:1;
}

.product-body h3{
font-size:18px;
margin-bottom:6px;
}

.product-body .desc{
font-size:14px;
color:#666;
height:36px;
overflow:hidden;
margin-bottom:6px;
}

.price{
font-weight:bold;
margin-bottom:6px;
}

.stock{
color:green;
font-size:14px;
margin-bottom:10px;
}

.qty-box{
display:flex;
align-items:center;
gap:8px;
margin-bottom:10px;
}
.qty-box input{
width:55px;padding:6px;border-radius:6px;border:1px solid #ccc;text-align:center;
}

.buy-btn{
margin-top:auto;
padding:10px;
background:#111;
color:white;
border:none;
border-radius:8px;
font-weight:bold;
cursor:pointer;
transition:0.3s;
text-decoration:none;
text-align:center;
display:block;
}
.buy-btn:hover{
background:#e74c3c;
}

/* ============ FOOTER ============ */
.footer{
background:#111;
color:#aaa;
text-align:center;
padding:25px;
margin-top:40px;
font-size:14px;
}
</style>
</head>

<body>

<header class="header">
    <div class="logo">KickFit</div>

    <nav class="nav">
        <ul>
            <li><a href="index.php">Shop</a></li>
            <?php if(!isset($_SESSION['user_id'])){ ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Signup</a></li>
            <?php } else { ?>
                <li><a href="admin/dashboard.php">Dashboard</a></li>
            <?php } ?>
        </ul>
    </nav>
</header>

<!-- HERO -->
<section class="hero">
    <div class="hero-text">
        <h1>Move Different. Walk Bold.</h1>
        <p>Performance meets street style.</p>
        <a href="index.php" class="hero-btn">Shop Collection</a>
    </div>
</section>

<!-- CATEGORY STRIP -->
<div class="category-strip">
    <?php while($row_category=mysqli_fetch_assoc($result_category)){ ?>
        <a href="index.php?category_name=<?php echo $row_category['name'];?>">
            <?php echo ucfirst($row_category['name']);?>
        </a>
    <?php } ?>
</div>

<h2 class="section-title" id="shop">Popular Right Now</h2>

<section class="products">
<?php while($row=mysqli_fetch_assoc($result_product_category)){ ?>
    <div class="product">
        <img src="image/<?php echo $row['image'];?>">
        <div class="product-body">
            <h3><?php echo $row['name'];?></h3>
            <div class="desc"><?php echo $row['description'];?></div>
            <div class="price">Rs. <?php echo $row['price'];?></div>
            <div class="stock">Stock: <?php echo $row['stock'];?></div>

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
    </div>
<?php } ?>
</section>

<footer class="footer">© KickFit — Fashion Forward Footwear</footer>

</body>
</html>
