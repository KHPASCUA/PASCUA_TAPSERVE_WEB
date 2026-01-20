<?php
session_start();
include "../config/db.php";

// --- Functions ---
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function addUser($fullname, $username, $password, $role) {
    global $conn;
    $fullname = sanitizeInput($fullname);
    $username = sanitizeInput($username);
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = sanitizeInput($role);

    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $fullname, $username, $hashed_password, $role);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return false; // Error in preparing the statement
    }
}

function deleteUser($id) {
    global $conn;
    $id = (int)$id;

    // Use prepared statement to prevent SQL injection
    $sql = "DELETE FROM users WHERE id = ?";
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

function updateUser($id, $fullname, $username, $password, $role) {
    global $conn;
    $id = (int)$id;
    $fullname = sanitizeInput($fullname);
    $username = sanitizeInput($username);
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = sanitizeInput($role);

    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE users SET fullname = ?, username = ?, password = ?, role = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssi", $fullname, $username, $hashed_password, $role, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return false; // Error in preparing the statement
    }
}

function getUsers() {
    global $conn;
    $sql = "SELECT * FROM users";
    return mysqli_query($conn, $sql);
}

// --- Process Form Submissions ---
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_user'])) {
        $fullname = isset($_POST['fullname']) ? sanitizeInput($_POST['fullname']) : '';
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? sanitizeInput($_POST['role']) : '';
        if (addUser($fullname, $username, $password, $role)) {
            $message = "<p class='success'>User added successfully!</p>";
        } else {
            $message = "<p class='error'>Error adding user: " . mysqli_error($conn) . "</p>";
        }
    } elseif (isset($_POST['update_user'])) {
        $edit_id = (int)$_POST['edit_id'];
        $fullname = isset($_POST['fullname']) ? sanitizeInput($_POST['fullname']) : '';
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? sanitizeInput($_POST['role']) : '';
        if (updateUser($edit_id, $fullname, $username, $password, $role)) {
            $message = "<p class='success'>User updated successfully!</p>";
        } else {
            $message = "<p class='error'>Error updating user: " . mysqli_error($conn) . "</p>";
        }
    }
}

if (isset($_GET['delete_user'])) {
    $delete_id = (int)$_GET['delete_user'];
    if (deleteUser($delete_id)) {
        $message = "<p class='success'>User deleted successfully!</p>";
    } else {
        $message = "<p class='error'>Error deleting user: " . mysqli_error($conn) . "</p>";
    }
}
// --- HTML Output ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users | TapServe</title>
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
        <h2>Manage Users</h2>

        <?php echo $message; ?>

        <form method="POST">
             <label for="fullname">Full Name:</label><br>
            <input type="text" id="fullname" name="fullname" placeholder="Full Name" required><br>

            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" placeholder="Username" required><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" placeholder="Password" required><br>

            <label for="role">Role:</label><br>
            <input type="text" id="role" name="role" placeholder="Role" required><br>

            <button type="submit" name="add_user">Add User</button>
        </form>

        <h3>Users List</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = getUsers();
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                             <td>" . htmlspecialchars($row['fullname']) . "</td>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['role']) . "</td>
                            <td>
                                <a href='?delete_user=" . (int)$row['id'] . "'>Delete</a> |
                                <a href='?edit_user=" . (int)$row['id'] . "'>Edit</a>
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
        if (isset($_GET['edit_user'])) {
            $edit_id = (int)$_GET['edit_user'];
            $edit_sql = "SELECT * FROM users WHERE id = ?";
             $stmt = mysqli_prepare($conn, $edit_sql);
             if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $edit_id);
                mysqli_stmt_execute($stmt);
                $edit_result = mysqli_stmt_get_result($stmt);
            if ($edit_result && $edit_row = mysqli_fetch_assoc($edit_result)) {
                ?>
                <h3>Edit User</h3>
                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_row['id']); ?>">

                     <label for="fullname">Full Name:</label><br>
                     <input type="text" id="fullname" name="fullname" placeholder="Full Name" value="<?php echo htmlspecialchars($edit_row['fullname']); ?>" required><br>

                    <label for="username">Username:</label><br>
                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($edit_row['username']); ?>" required><br>

                    <label for="password">Password:</label><br>
                    <input type="password" id="password" name="password" placeholder="Password" required><br>

                     <label for="role">Role:</label><br>
                    <input type="text" id="role" name="role" placeholder="Role" value="<?php echo htmlspecialchars($edit_row['role']); ?>" required><br>

                    <button type="submit" name="update_user">Update User</button>
                </form>
                <?php
            } else {
                echo "<p class='error'>Error fetching user data for editing.</p>";
            }
             } else {
                echo "<p class='error'>Error preparing statement: " . mysqli_error($conn) . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>