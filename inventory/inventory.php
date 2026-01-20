<?php
session_start();
include "../config/db.php";

// --- Functions ---
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function addInventoryItem($name, $description, $price, $quantity) {
    global $conn;
    $name = sanitizeInput($name);
    $description = sanitizeInput($description);
    $price = (float)$price;
    $quantity = (int)$quantity;
    $sql = "INSERT INTO inventory (item_name, description, price, quantity) VALUES ('$name', '$description', $price, $quantity)";
    return mysqli_query($conn, $sql);
}

function deleteInventoryItem($id) {
    global $conn;
    $id = (int)$id;
    $sql = "DELETE FROM inventory WHERE id = $id";
    return mysqli_query($conn, $sql);
}

function updateInventoryItem($id, $name, $description, $price, $quantity) {
    global $conn;
    $id = (int)$id;
    $name = sanitizeInput($name);
    $description = sanitizeInput($description);
    $price = (float)$price;
    $quantity = (int)$quantity;
    $sql = "UPDATE inventory SET item_name = '$name', description = '$description', price = $price, quantity = $quantity WHERE id = $id";
    return mysqli_query($conn, $sql);
}

function getInventoryItems() {
    global $conn;
    $sql = "SELECT * FROM inventory";
    return mysqli_query($conn, $sql);
}

// --- Process Form Submissions ---
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_inventory'])) {
        $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        if (addInventoryItem($name, $description, $price, $quantity)) {
            $message = "<p class='success'>Inventory item added successfully!</p>";
        } else {
            $message = "<p class='error'>Error adding inventory item: " . mysqli_error($conn) . "</p>";
        }
    } elseif (isset($_POST['update_inventory'])) {
        $edit_id = (int)$_POST['edit_id'];
         $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        if (updateInventoryItem($edit_id, $name, $description, $price, $quantity)) {
            $message = "<p class='success'>Inventory item updated successfully!</p>";
        } else {
            $message = "<p class='error'>Error updating inventory item: " . mysqli_error($conn) . "</p>";
        }
    }
}

if (isset($_GET['delete_inventory'])) {
    $delete_id = (int)$_GET['delete_inventory'];
    if (deleteInventoryItem($delete_id)) {
        $message = "<p class='success'>Inventory item deleted successfully!</p>";
    } else {
        $message = "<p class='error'>Error deleting inventory item: " . mysqli_error($conn) . "</p>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Inventory | TapServe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="../asset/css/style.css">
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-button">Back to Dashboard</a>
        <h2>Manage Inventory</h2>

        <?php echo $message; ?>

        <form method="POST">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" placeholder="Name" required><br>

            <label for="description">Description:</label><br>
            <input type="text" id="description" name="description" placeholder="Description"><br>

            <label for="price">Price:</label><br>
            <input type="number" id="price" name="price" placeholder="Price" step="0.01" required><br>

            <label for="quantity">Quantity:</label><br>
            <input type="number" id="quantity" name="quantity" placeholder="Quantity" required><br>

            <button type="submit" name="add_inventory">Add Inventory</button>
        </form>

        <h3>Inventory List</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getInventoryItems();
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['item_name']) . "</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                            <td>" . htmlspecialchars($row['price']) . "</td>
                            <td>" . htmlspecialchars($row['quantity']) . "</td>
                            <td>
                                <a href='?delete_inventory=" . (int)$row['id'] . "'>Delete</a> |
                                <a href='?edit_inventory=" . (int)$row['id'] . "'>Edit</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Error fetching data: " . mysqli_error($conn) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php
        // Edit functionality
        if (isset($_GET['edit_inventory'])) {
            $edit_id = (int)$_GET['edit_inventory'];
            $edit_sql = "SELECT * FROM inventory WHERE id = $edit_id";
            $edit_result = mysqli_query($conn, $edit_sql);
            if ($edit_result && $edit_row = mysqli_fetch_assoc($edit_result)) {
                ?>
                <h3>Edit Inventory Item</h3>
                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_row['id']); ?>">

                    <label for="name">Name:</label><br>
                    <input type="text" id="name" name="name" placeholder="Name" value="<?php echo htmlspecialchars($edit_row['item_name']); ?>" required><br>

                    <label for="description">Description:</label><br>
                    <input type="text" id="description" name="description" placeholder="Description" value="<?php echo htmlspecialchars($edit_row['description']); ?>"><br>

                    <label for="price">Price:</label><br>
                    <input type="number" id="price" name="price" placeholder="Price" step="0.01" value="<?php echo htmlspecialchars($edit_row['price']); ?>" required><br>

                    <label for="quantity">Quantity:</label><br>
                    <input type="number" id="quantity" name="quantity" placeholder="Quantity" value="<?php echo htmlspecialchars($edit_row['quantity']); ?>" required><br>

                    <button type="submit" name="update_inventory">Update Inventory</button>
                </form>
                <?php
            } else {
                echo "<p class='error'>Error fetching inventory item data for editing.</p>";
            }
        }
        ?>
    </div>
</body>
</html>