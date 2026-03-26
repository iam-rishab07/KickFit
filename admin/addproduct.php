<?php
include "../db.php";
session_start();

// Security: Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== "admin") {
    header("Location: ../index.php");
    exit();
}

// Fetch categories for the dropdown
$cat_query = "SELECT name FROM categories";
$cat_result = mysqli_query($conn, $cat_query);

$message = "";
$msg_type = "";

if (isset($_POST['submit'])) {
    // Collect and sanitize data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    // Image Handling
    $image_name = $_FILES['image']['name'];
    $temp_location = $_FILES['image']['tmp_name'];
    $upload_location = "../image/";
    
    // Simple validation
    if (empty($image_name)) {
        $message = "Please upload an image!";
        $msg_type = "error";
    } else {
        // Prepared Statement for insertion
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $image_name, $category_name);

        if ($stmt->execute()) {
            move_uploaded_file($temp_location, $upload_location . $image_name);
            $message = "Sneaker added to inventory successfully!";
            $msg_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $msg_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | KickFit Admin</title>
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

        /* Reuse your improved Sidebar styling */
        .sidebar {
            width: 260px; height: 100vh; background: var(--sidebar-dark);
            position: fixed; padding: 30px 0; display: flex; flex-direction: column;
        }
        .sidebar-brand { color: var(--white); font-size: 22px; font-weight: 800; text-align: center; margin-bottom: 40px; }
        .sidebar-brand span { color: var(--primary); }
        .sidebar-nav { list-style: none; flex: 1; }
        .sidebar-nav li { padding: 5px 20px; }
        .sidebar-nav a {
            display: flex; align-items: center; padding: 12px 15px; color: #94a3b8;
            text-decoration: none; border-radius: 8px; font-size: 14px; transition: var(--transition);
        }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(192, 57, 43, 0.1); color: var(--primary); }
        .sidebar-nav a i { width: 30px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 50px; display: flex; justify-content: center; }

        .form-container {
            background: var(--white); width: 100%; max-width: 700px;
            padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .form-header { margin-bottom: 30px; }
        .form-header h1 { font-size: 24px; font-weight: 800; }
        .form-header p { color: #64748b; font-size: 14px; margin-top: 5px; }

        /* Notification */
        .alert {
            padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 14px; font-weight: 600;
            display: <?= $message ? 'block' : 'none' ?>;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; color: #475569; }
        
        input, select, textarea {
            width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 15px; transition: var(--transition); outline: none;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1); }

        .image-preview-box {
            width: 100%; height: 180px; border: 2px dashed #cbd5e1; border-radius: 12px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            margin-top: 10px; overflow: hidden; position: relative;
        }
        #preview-img { width: 100%; height: 100%; object-fit: contain; display: none; }

        .btn-submit {
            width: 100%; padding: 15px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: var(--transition); margin-top: 10px;
        }
        .btn-submit:hover { background: #922b21; transform: translateY(-2px); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">KICK<span>FIT</span></div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="addproduct.php" class="active"><i class="fa-solid fa-plus-circle"></i> Add Product</a></li>
        <li><a href="displayproduct.php"><i class="fa-solid fa-list"></i> View Products</a></li>
        <li><a href="vieworders.php"><i class="fa-solid fa-truck"></i> Orders</a></li>
        <li><a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>

<main class="main-content">
    <div class="form-container">
        <div class="form-header">
            <h1>Add New Sneaker</h1>
            <p>Enter the details below to add a new product to the KickFit catalog.</p>
        </div>

        <div class="alert alert-<?= $msg_type ?>"><?= $message ?></div>

        <form action="addproduct.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" placeholder="e.g. Air Jordan 1 Retro" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe the materials, fit, and style..." required></textarea>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Price (₹)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Initial Stock</label>
                    <input type="number" name="stock" placeholder="Qty" required>
                </div>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_name" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php while($row = mysqli_fetch_assoc($cat_result)): ?>
                        <option value="<?= $row['name'] ?>"><?= ucfirst($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" id="image-input" accept="image/*" required>
                <div class="image-preview-box" id="preview-container">
                    <i class="fa-solid fa-cloud-arrow-up" id="upload-icon" style="font-size: 30px; color: #94a3b8;"></i>
                    <p id="upload-text" style="color: #94a3b8; font-size: 12px; margin-top: 10px;">Preview will appear here</p>
                    <img src="" id="preview-img">
                </div>
            </div>

            <button type="submit" name="submit" class="btn-submit">Add Product to Inventory</button>
        </form>
    </div>
</main>

<script>
    // Image Preview Logic
    const imageInput = document.getElementById('image-input');
    const previewImg = document.getElementById('preview-img');
    const uploadIcon = document.getElementById('upload-icon');
    const uploadText = document.getElementById('upload-text');

    imageInput.onchange = evt => {
        const [file] = imageInput.files;
        if (file) {
            previewImg.src = URL.createObjectURL(file);
            previewImg.style.display = 'block';
            uploadIcon.style.display = 'none';
            uploadText.style.display = 'none';
        }
    }
</script>

</body>
</html>