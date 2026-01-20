<?php
session_start(); // Start the session
include "../config/db.php";

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$message = ""; // Variable to store success/error messages

// Process form submission
if (isset($_POST['add'])) {
    // Validate and sanitize the table ID
    $table_id = filter_input(INPUT_POST, 'table', FILTER_VALIDATE_INT);
    if ($table_id === false) {
        $message = "<p class='error'>Invalid table ID.</p>";
    } else {
        // Construct the SQL query using prepared statements
        $sql = "INSERT INTO orders (table_id) VALUES (?)";
        $stmt = mysqli_prepare($conn, $sql);

        // Bind the parameter
        mysqli_stmt_bind_param($stmt, "i", $table_id);

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            $message = "<p class='success'>Order created successfully!</p>";
        } else {
            $message = "<p class='error'>Error creating order: " . mysqli_error($conn) . "</p>";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    }
}

// Retrieve orders data
$sql = "
    SELECT orders.id, tables.table_number
    FROM orders
    LEFT JOIN tables ON orders.table_id = tables.id";
$data = mysqli_query($conn, $sql);

if (!$data) {
    echo "Error: " . mysqli_error($conn);
}

// Retrieve tables data for dropdown
$tables = $conn->query("SELECT id, table_number FROM tables");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders</title>
    <link rel="stylesheet" href="../asset/css/style.css">
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
    <a href="../dashboard.php" class="back-button">← Back to Dashboard</a>
    <h2>Orders</h2>

    <?php echo $message; ?>

    <form method="POST">
        <label for="table">Select Table:</label>
        <select name="table" id="table" required>
            <?php
            $tables_result = $conn->query("SELECT id, table_number FROM tables");
            if ($tables_result) {
                while($table = mysqli_fetch_assoc($tables_result)): ?>
                    <option value="<?= htmlspecialchars($table['id']) ?>">
                        <?= htmlspecialchars($table['table_number']) ?>
                    </option>
                <?php endwhile;
            } else {
                echo "Error retrieving tables: " . mysqli_error($conn);
            }
            ?>
        </select>
        <button type="submit" name="add">Create Order</button>
    </form>

    <h3>Order List</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Table</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($data) {
                while($r = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['id']) ?></td>
                        <td><?= htmlspecialchars($r['table_number']) ?></td>
                    </tr>
                <?php endwhile;
            } else {
                echo "<tr><td colspan='2'>No orders found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</div>
</body>
</html>