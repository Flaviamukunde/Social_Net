<?php
session_start();
include "connection.php";

if (!isset($_SESSION["userLogged"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["userLogged"];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id' LIMIT 1");
$user = mysqli_fetch_assoc($query);

// CRUD
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_product'])) {
        $name = mysqli_real_escape_string($conn, $_POST['productName']);
        $qty = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        mysqli_query($conn, "INSERT INTO products (productName, quantity, price) VALUES('$name', '$qty', '$price')");
        header("Location: dashboard.php");
        exit();
    }

    if (isset($_POST['edit_product'])) {
        $id = intval($_POST['product_id']);
        $name = mysqli_real_escape_string($conn, $_POST['productName']);
        $qty = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        mysqli_query($conn, "UPDATE products SET productName='$name', quantity='$qty', price='$price' WHERE id='$id'");
        header("Location: dashboard.php");
        exit();
    }

    if (isset($_POST['delete_product'])) {
        $id = intval($_POST['product_id']);
        mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
        header("Location: dashboard.php");
        exit();
    }
}

$products_query = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instagram Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Rubik', sans-serif;
            background: #121212;
            color: #fff;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #111;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 30px;
            border-right: 1px solid #222;
        }

        .sidebar h1 {
            font-family: 'Brush Script MT', cursive;
            color: #fff;
            font-size: 32px;
            margin-bottom: 40px;
        }

        .sidebar nav {
            width: 100%;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #aaa;
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            margin: 5px 10px;
            transition: 0.3s;
        }

        .sidebar nav a i {
            margin-right: 15px;
            font-size: 18px;
        }

        .sidebar nav a:hover {
            background: linear-gradient(90deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            color: #fff;
        }

        /* Main */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: #111;
            border-bottom: 1px solid #222;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header h2 {
            font-weight: 500;
        }

        header form button {
            background: #e60023;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        header form button:hover {
            background: #ff3366;
        }

        /* Container */
        .container {
            padding: 20px 40px;
            flex: 1;
            overflow: auto;
        }

        .card {
            background: #111;
            border-radius: 15px;
            padding: 30px;
            border: 1px solid #222;
        }

        /* Status circles */
        .status-bar {
            display: flex;
            margin-bottom: 20px;
            gap: 15px;
        }

        .status {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #f09433;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .status img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        .status:hover {
            transform: scale(1.1);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #222;
        }

        table th {
            font-weight: 600;
            border-radius: 8px;
        }

        table tr:hover {
            background: #1e1e1e;
            color: #fff;
        }

        .manage-btn {
            background: #0095f6;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        .manage-btn:hover {
            background: #1da1f2;
        }

        .delete-btn {
            background: #ff4444;
        }

        .delete-btn:hover {
            background: #ff6666;
        }

        /* Add button */
        .add-btn {
            background: #00c853;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .add-btn:hover {
            background: #00e676;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            justify-content: center;
            align-items: center;
            z-index: 200;
        }

        .modal-content {
            background: #111;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #222;
        }

        .modal-content h3 {
            margin-bottom: 20px;
        }

        .modal-content input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #333;
            background: #222;
            color: #fff;
        }

        .modal-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .close-btn {
            background: #ff4444;
            color: #fff;
            margin-left: 10px;
        }

        .close-btn:hover {
            background: #ff6666;
        }

        /* Responsive */
        @media(max-width:900px) {
            .sidebar {
                display: none;
            }

            .main {
                flex: 1;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h1>Instagram</h1>
        <nav>
            <a href="#"><i class="fas fa-home"></i> Home</a>
            <a href="#"><i class="fas fa-search"></i> Search</a>
            <a href="#"><i class="fas fa-compass"></i> Explore</a>
            <a href="#"><i class="fas fa-video"></i> Reels</a>
            <a href="#"><i class="fas fa-envelope"></i> Messages</a>
            <a href="#"><i class="fas fa-heart"></i> Notifications</a>
            <a href="#"><i class="fas fa-user"></i> Profile</a>
        </nav>
    </div>

    <div class="main">
        <header>
            <h2>Welcome, <?php echo $user["username"]; ?></h2>
            <form action="logout.php" method="POST">
                <button type="submit">Logout</button>
            </form>
        </header>

        <div class="container">
            <div class="card">

                <!-- Status bar -->
                <div class="status-bar">
                    <div class="status"><img src="https://i.pravatar.cc/50?img=1" alt="user"></div>
                    <div class="status"><img src="https://i.pravatar.cc/50?img=2" alt="user"></div>
                    <div class="status"><img src="https://i.pravatar.cc/50?img=3" alt="user"></div>
                    <div class="status"><img src="https://i.pravatar.cc/50?img=4" alt="user"></div>
                    <div class="status"><img src="https://i.pravatar.cc/50?img=5" alt="user"></div>
                </div>
                <div class="user-info">
                    <h2>Product Management System</h2>
                    <div style="display: flex; justify-content:end;">
                        <button class="add-btn" onclick="openModal('add')">+ Add Product</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total Price</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        while ($row = mysqli_fetch_assoc($products_query)): ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo htmlspecialchars($row['productName']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                <td>$<?php echo number_format($row['quantity'] * $row['price'], 2); ?></td>
                                <td>
                                    <button class="manage-btn" onclick="openModal('edit', <?php echo $row['id']; ?>,'<?php echo htmlspecialchars($row['productName']); ?>', <?php echo $row['quantity']; ?>, <?php echo $row['price']; ?>)">Edit</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="manage-btn delete-btn" name="delete_product">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php $i++;
                        endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <h3 id="modalTitle">Add Product</h3>
            <form method="POST" id="modalForm">
                <input type="hidden" name="product_id" id="product_id">
                <input type="text" name="productName" id="productName" placeholder="Product Name" required>
                <input type="number" name="quantity" id="quantity" placeholder="Quantity" min="1" required>
                <input type="number" step="0.01" name="price" id="price" placeholder="Price" min="0" required>
                <button type="submit" name="add_product" id="submitBtn">Add Product</button>
                <button type="button" class="close-btn" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(type, id = '', name = '', qty = '', price = '') {
            document.getElementById('productModal').style.display = 'flex';
            if (type === 'add') {
                document.getElementById('modalTitle').innerText = 'Add Product';
                document.getElementById('submitBtn').innerText = 'Add Product';
                document.getElementById('submitBtn').name = 'add_product';
                document.getElementById('product_id').value = '';
                document.getElementById('productName').value = '';
                document.getElementById('quantity').value = '';
                document.getElementById('price').value = '';
            } else {
                document.getElementById('modalTitle').innerText = 'Edit Product';
                document.getElementById('submitBtn').innerText = 'Save Changes';
                document.getElementById('submitBtn').name = 'edit_product';
                document.getElementById('product_id').value = id;
                document.getElementById('productName').value = name;
                document.getElementById('quantity').value = qty;
                document.getElementById('price').value = price;
            }
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
    </script>

</body>

</html>