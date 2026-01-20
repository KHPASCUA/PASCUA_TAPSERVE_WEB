<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); // Redirect to login page if not logged in
    exit();
}

include "../config/db.php";

// --- Functions ---
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function addTable($table_number, $status) {
    global $conn;
    $table_number = sanitizeInput($table_number);
    $status = sanitizeInput($status);

    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO tables (table_number, status) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $table_number, $status);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return false; // Error in preparing the statement
    }
}

function deleteTable($id) {
    global $conn;
    $id = (int)$id;

    // Use prepared statement to prevent SQL injection
    $sql = "DELETE FROM tables WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return false; // Error in preparing the statement
    }
}

function updateTable($id, $table_number, $status) {
    global $conn;
    $id = (int)$id;
    $table_number = sanitizeInput($table_number);
    $status = sanitizeInput($status);

    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE tables SET table_number = ?, status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $table_number, $status, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return false; // Error in preparing the statement
    }
}

function getTables() {
    global $conn;
    $sql = "SELECT * FROM tables";
    return mysqli_query($conn, $sql);
}

// --- Process Form Submissions ---
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_table'])) {
        $table_number = $_POST['table_number'];
        $status = $_POST['status'];
        if (addTable($table_number, $status)) {
            $message = "<p class='success'>Table added successfully!</p>";
        } else {
            $message = "<p class='error'>Error adding table: " . mysqli_error($conn) . "</p>";
        }
    } elseif (isset($_POST['update_table'])) {
        $edit_id = (int)$_POST['edit_id'];
        $table_number = $_POST['table_number'];
        $status = $_POST['status'];
        if (updateTable($edit_id, $table_number, $status)) {
            $message = "<p class='success'>Table updated successfully!</p>";
        } else {
            $message = "<p class='error'>Error updating table: " . mysqli_error($conn) . "</p>";
        }
    }
}

if (isset($_GET['delete_table'])) {
    $delete_id = (int)$_GET['delete_table'];
    if (deleteTable($delete_id)) {
        $message = "<p class='success'>Table deleted successfully!</p>";
    } else {
        $message = "<p class='error'>Error deleting table: " . mysqli_error($conn) . "</p>";
    }
}

// --- HTML Output ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Tables | TapServe</title>
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
        <h2>Manage Tables</h2>

        <?php echo $message; ?>

        <form method="POST">
            <label for="table_number">Table Number:</label><br>
            <input type="text" id="table_number" name="table_number" placeholder="Table Number" required><br>

            <label for="status">Status:</label><br>
            <select id="status" name="status">
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="reserved">Reserved</option>
            </select><br>

            <button type="submit" name="add_table">Add Table</button>
        </form>

        <h3>Tables List</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Table Number</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getTables();
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['table_number']) . "</td>
                            <td>" . htmlspecialchars($row['status']) . "</td>
                            <td>
                                <a href='?delete_table=" . (int)$row['id'] . "'>Delete</a> |
                                <a href='?edit_table=" . (int)$row['id'] . "'>Edit</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Error fetching data: " . mysqli_error($conn) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php
        // Edit functionality
        if (isset($_GET['edit_table'])) {
            $edit_id = (int)$_GET['edit_table'];
             $edit_sql = "SELECT * FROM tables WHERE id = ?";
            $stmt = mysqli_prepare($conn, $edit_sql);
             if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $edit_id);
                mysqli_stmt_execute($stmt);
                $edit_result = mysqli_stmt_get_result($stmt);

            if ($edit_result && $edit_row = mysqli_fetch_assoc($edit_result)) {
                ?>
                <h3>Edit Table</h3>
                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_row['id']); ?>">

                    <label for="table_number">Table Number:</label><br>
                    <input type="text" id="table_number" name="table_number" placeholder="Table Number" value="<?php echo htmlspecialchars($edit_row['table_number']); ?>" required><br>

                    <label for="status">Status:</label><br>
                    <select id="status" name="status">
                        <option value="available" <?php if ($edit_row['status'] == 'available') echo 'selected'; ?>>Available</option>
                        <option value="occupied" <?php if ($edit_row['status'] == 'occupied') echo 'selected'; ?>>Occupied</option>
                        <option value="reserved" <?php if ($edit_row['status'] == 'reserved') echo 'selected'; ?>>Reserved</option>
                    </select><br>

                    <button type="submit" name="update_table">Update Table</button>
                </form>
                <?php
            } else {
                echo "<p class='error'>Error fetching table data for editing.</p>";
            }
        } else {
                echo "<p class='error'>Error preparing statement: " . mysqli_error($conn) . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>