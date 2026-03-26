<?php
include "../db.php";
session_start();

/* ---------- ACCESS CONTROL ---------- */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != "admin") {
    header("Location: ../index.php");
    exit();
}

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($product_id == 0) {
    header("Location: displayproduct.php");
    exit();
}

/* ---------- FETCH DATA FOR FORM ---------- */
$cat_result = mysqli_query($conn, "SELECT * FROM categories");

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

/* ---------- UPDATE LOGIC ---------- */
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_name = $_POST['category_name'];
    
    // Image Handling
    $image = $_FILES['image']['name'];
    if ($image) {
        $temp_location = $_FILES['image']['tmp_name'];
        $upload_location = "../image/";
        move_uploaded_file($temp_location, $upload_location . $image);
        
        $sql = "UPDATE products SET name=?, description=?, price=?, stock=?, image=?, category_name=? WHERE id=?";
        $update_stmt = $conn->prepare($sql);
        $update_stmt->bind_param("ssdissi", $name, $description, $price, $stock, $image, $category_name, $product_id);
    } else {
        $sql = "UPDATE products SET name=?, description=?, price=?, stock=?, category_name=? WHERE id=?";
        $update_stmt = $conn->prepare($sql);
        $update_stmt->bind_param("ssdssi", $name, $description, $price, $stock, $category_name, $product_id);
    }

    if ($update_stmt->execute()) {
        header("Location: displayproduct.php?msg=updated");
        exit();
    } else {
        $error = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product | KickFit Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #c0392b;
            --sidebar-dark: #1a1c1e;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); display: flex; }

        /* Sidebar Styles (Consistent with your dashboard) */
        .sidebar {
            width: 240px; height: 100vh; background: var(--sidebar-dark);
            position: fixed; padding: 25px 0;
        }
        .sidebar ul li { list-style: none; }
        .sidebar ul li a {
            padding: 15px 25px; display: block; text-decoration: none; color: #fff;
            font-weight: 500; transition: var(--transition); border-left: 4px solid transparent;
        }
        .sidebar ul li a:hover { background: #922b21; border-left: 4px solid #fff; }

        /* Main Area */
        .main-content { margin-left: 240px; width: 100%; padding: 40px; display: flex; justify-content: center; align-items: flex-start; }

        .form-card {
            background: var(--white); width: 100%; max-width: 700px; padding: 40px;
            border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .form-card h2 { margin-bottom: 25px; font-weight: 800; color: var(--primary); }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
        
        input, select, textarea {
            width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 15px; transition: var(--transition); outline: none;
        }
        input:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1); }

        .current-img-box {
            display: flex; align-items: center; gap: 20px; padding: 15px;
            background: #f1f5f9; border-radius: 8px; margin-bottom: 15px;
        }
        .current-img-box img { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }

        .btn-row { display: flex; gap: 15px; margin-top: 10px; }
        .button {
            flex: 2; padding: 14px; background: var(--primary); color: #fff;
            border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s;
        }
        .button:hover { background: #922b21; }
        .btn-cancel {
            flex: 1; padding: 14px; background: #e2e8f0; color: #475569;
            text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="displayproduct.php">View Products</a></li>
        <li><a href="vieworders.php">View Orders</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="form-card">
        <h2><i class="fa-solid fa-pen-to-square"></i> Update Product Details</h2>
        
        <?php if(isset($error)) echo "<p style='color:red; margin-bottom:15px;'>$error</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" required><?= htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Price (₹)</label>
                    <input type="number" step="0.01" name="price" value="<?= $product['price']; ?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock" value="<?= $product['stock']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Current Product Image</label>
                <div class="current-img-box">
                    <img src="../image/<?= $product['image']; ?>" alt="Product">
                    <div style="font-size: 13px; color: #64748b;">
                        <b>Filename:</b> <?= $product['image']; ?><br>
                        <span>Leave empty to keep current image</span>
                    </div>
                </div>
                <input type="file" name="image">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_name">
                    <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
                        <option value="<?= $cat['name']; ?>" <?= ($cat['name'] == $product['category_name']) ? 'selected' : ''; ?>>
                            <?= ucfirst($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="btn-row">
                <button type="submit" name="submit" class="button">Update Sneaker</button>
                <a href="displayproduct.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>