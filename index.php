<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
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
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php">shop</a>
        <a href="index.php"><img src="" alt=""></a>
        <nav>
            <ul>
                <li><a href="">login</a></li>
                <li><a href="register.php">signup</a></li>
                <li><a href="">dashboard</a></li>
            </ul>
        </nav>
    </header>
    <main class="main">
        <?php for($i=0;$i<15;$i++){
            ?>
        <div class="product"> 
            <img src="productimg/red-nike.jpg" alt="productimg">
            <h2>product title</h2>
            <p>product description</p>
            <p>product quantity</p>
            <p class="productprice">product price</p>
            <a href="$">Buy Now</a>
        </div>
        <div class="product"> 
            <img src="productimg/white-blue.jpg" alt="productimg">
            <h2>product title</h2>
            <p>product description</p>
            <p>product quantity</p>
            <p class="productprice">product price</p>
            <a href="$">Buy Now</a>
        </div>
        <?php
          }  ?>
    </main>
    <footer class="footer">
        <p>Copyright@: Rishi Enterprises</p>
    </footer>
</body>
</html>