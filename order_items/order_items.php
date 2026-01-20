<?php
session_start();
include "../config/db.php";

// --- Functions ---
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function addOrderItem($order_id, $item_name, $quantity) {
    global $conn;
    $order_id = (int)$order_id;
    $item_name = sanitizeInput($item_name);
    $quantity = (int)$quantity;
    $sql = "INSERT INTO order_items (order_id, item_name, quantity) VALUES ($order_id, '$item_name', $quantity)";
    return mysqli_query($conn, $sql);
}

function deleteOrderItem($id) {
    global $conn;
    $id = (int)$id;
    $sql = "DELETE FROM order_items WHERE id = $id";
    return mysqli_query($conn, $sql);
}

function updateOrderItem($id, $order_id, $item_name, $quantity) {
    global $conn;
    $id = (int)$id;
    $order_id = (int)$order_id;
    $item_name = sanitizeInput($item_name);
    $quantity = (int)$quantity;
    $sql = "UPDATE order_items SET order_id = $order_id, item_name = '$item_name', quantity = $quantity WHERE id = $id";
    return mysqli_query($conn, $sql);
}

function getOrderItems() {
    global $conn;
    $sql = "SELECT * FROM order_items";
    return mysqli_query($conn, $sql);
}

// --- Process Form Submissions ---
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_order_item'])) {
        $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $item_name = isset($_POST['item_name']) ? sanitizeInput($_POST['item_name']) : '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        if (addOrderItem($order_id, $item_name, $quantity)) {
            $message = "<p class='success'>Order item added successfully!</p>";
        } else {
            $message = "<p class='error'>Error adding order item: " . mysqli_error($conn) . "</p>";
        }
    } elseif (isset($_POST['update_order_item'])) {
        $edit_id = (int)$_POST['edit_id'];
        $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $item_name = isset($_POST['item_name']) ? sanitizeInput($_POST['item_name']) : '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        if (updateOrderItem($edit_id, $order_id, $item_name, $quantity)) {
            $message = "<p class='success'>Order item updated successfully!</p>";
        } else {
            $message = "<p class='error'>Error updating order item: " . mysqli_error($conn) . "</p>";
        }
    }
}

if (isset($_GET['delete_order_item'])) {
    $delete_id = (int)$_GET['delete_order_item'];
    if (deleteOrderItem($delete_id)) {
        $message = "<p class='success'>Order item deleted successfully!</p>";
    } else {
        $message = "<p class='error'>Error deleting order item: " . mysqli_error($conn) . "</p>";
    }
}

// SPLIT POINT - REMEMBER THIS LINE// --- HTML Output ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Order Items | TapServe</title>
    <link rel="stylesheet" type="text/css" href="../asset/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .back-button {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #555;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-button:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-button">Back to Dashboard</a>
        <h2>Manage Order Items</h2>

        <?php echo $message; ?>

        <form method="POST">
            <label for="order_id">Order ID:</label><br>
            <input type="number" id="order_id" name="order_id" placeholder="Order ID" required><br>

            <label for="item_name">Item Name:</label><br>
            <input type="text" id="item_name" name="item_name" placeholder="Item Name" required><br>

            <label for="quantity">Quantity:</label><br>
            <input type="number" id="quantity" name="quantity" placeholder="Quantity" required><br>

            <button type="submit" name="add_order_item">Add Item</button>
        </form>

        <h3>Order Items List</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Order ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getOrderItems();
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['order_id']) . "</td>
                            <td>" . htmlspecialchars($row['item_name']) . "</td>
                            <td>" . htmlspecialchars($row['quantity']) . "</td>
                            <td>
                                <a href='?delete_order_item=" . (int)$row['id'] . "'>Delete</a> |
                                <a href='?edit_order_item=" . (int)$row['id'] . "'>Edit</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Error fetching data: " . mysqli_error($conn) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php
        // Edit functionality
        if (isset($_GET['edit_order_item'])) {
            $edit_id = (int)$_GET['edit_order_item'];
            $edit_sql = "SELECT * FROM order_items WHERE id = $edit_id";
            $edit_result = mysqli_query($conn, $edit_sql);
            if ($edit_result && $edit_row = mysqli_fetch_assoc($edit_result)) {
                ?>
                <h3>Edit Order Item</h3>
                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_row['id']); ?>">

                    <label for="order_id">Order ID:</label><br>
                    <input type="number" id="order_id" name="order_id" placeholder="Order ID" value="<?php echo htmlspecialchars($edit_row['order_id']); ?>" required><br>

                    <label for="item_name">Item Name:</label><br>
                    <input type="text" id="item_name" name="item_name" placeholder="Item Name" value="<?php echo htmlspecialchars($edit_row['item_name']); ?>" required><br>

                    <label for="quantity">Quantity:</label><br>
                    <input type="number" id="quantity" name="quantity" placeholder="Quantity" value="<?php echo htmlspecialchars($edit_row['quantity']); ?>" required><br>

                    <button type="submit" name="update_order_item">Update Item</button>
                </form>
                <?php
            } else {
                echo "<p class='error'>Error fetching order item data for editing.</p>";
            }
        }
        ?>
    </div>
</body>
</html>