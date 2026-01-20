<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>TapServe Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="asset/css/style.css">
    <style>
        body {
            font-family: 'Lora', serif; /* Elegant, slightly vintage font */
            background-color: #333; /* Dark gray background */
            color: #eee; /* Light text */
            margin: 0;
            padding: 0;
            background-image: url("asset/img/dark-wood-texture.jpg"); /* Replace with your image */
            background-size: cover;
            background-blend-mode: multiply; /* Darken the background */
        }

        .dashboard-container {
            width: 90%;
            margin: 20px auto;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent dark background */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.8); /* Stronger shadow */
            border: 1px solid rgba(255, 255, 255, 0.1); /* Subtle border */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2); /* Light border */
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 2.8em;
            color: #f0ad4e; /* Golden color for the title */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Shadow for depth */
        }

        .header div {
            text-align: right;
        }

        .header a {
            color: #f0ad4e;
            text-decoration: none;
            margin-left: 15px;
            font-weight: 600;
            transition: color 0.3s ease;
            border: 1px solid transparent;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .header a:hover {
            color: #fff;
            text-decoration: none;
            border-color: #f0ad4e;
        }

        .welcome-message {
            font-size: 1.4em;
            color: #ddd;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* Grid Layout */
        .manage-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }

        .manage-item {
            background-color: rgba(0, 0, 0, 0.7); /* Darker items */
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.7);
        }

        .manage-item:hover {
            background-color: rgba(0, 0, 0, 0.9);
            transform: translateY(-5px);
        }

        .manage-item a {
            display: block;
            text-decoration: none;
            color: #fff;
            font-weight: 600;
            font-size: 1.2em;
            transition: color 0.3s ease;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }

        .manage-item a:hover {
            color: #f0ad4e;
        }

        .manage-item i {
            font-size: 2.5em;
            margin-bottom: 15px;
            color: #f0ad4e;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>TapServe</h1>
            <div>
                Welcome, <?php echo htmlspecialchars($username); ?> | <a href="auth/logout.php">Logout</a>
            </div>
        </div>

        <p class="welcome-message">Welcome to your TapServe dashboard!</p>

        <div class="manage-grid">
            <div class="manage-item">
                <a href="inventory/inventory.php"><i class="fas fa-boxes"></i>Inventory</a>
            </div>
            <div class="manage-item">
                <a href="tables/tables.php"><i class="fas fa-table"></i>Tables</a>
            </div>
            <div class="manage-item">
                <a href="orders/orders.php"><i class="fas fa-shopping-cart"></i>Orders</a>
            </div>
            <div class="manage-item">
                <a href="order_items/order_items.php"><i class="fas fa-list"></i>Order Items</a>
            </div>
            <div class="manage-item">
                <a href="users/users.php"><i class="fas fa-users"></i>Users</a>
            </div>
        </div>
    </div>
</body>
</html>