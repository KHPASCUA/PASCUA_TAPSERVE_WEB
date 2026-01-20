<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../config/db.php";

    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = $_POST["password"];

    //echo "<p><b>Debugging Information:</b></p>"; // Start of debugging output

    //echo "<p>Entered Username: " . htmlspecialchars($username) . "</p>";
    //echo "<p>Entered Password: " . htmlspecialchars($password) . "</p>";

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
        //echo "<p>Error: " . $error . "</p>";
    } else {
        $sql = "SELECT id, username, password FROM users WHERE username = '$username'";
        //echo "<p>SQL Query: " . htmlspecialchars($sql) . "</p>"; // Show the SQL query

        $result = mysqli_query($conn, $sql);

        if ($result === false) {
            $error = "Database query failed: " . mysqli_error($conn);
            //echo "<p>Error: " . $error . "</p>";
        } elseif (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            //echo "<p>Username from DB: " . htmlspecialchars($row["username"]) . "</p>";
            //echo "<p>Hashed Password from DB: " . htmlspecialchars($row["password"]) . "</p>";

            if (password_verify($password, $row["password"])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["username"] = $row["username"];
                //echo "<p>Login successful! Redirecting to dashboard...</p>";
                header("Location: ../dashboard.php");
                exit();
            } else {
                $error = "Incorrect password.";
                //echo "<p>Error: " . $error . "</p>";
            }
        } else {
            $error = "Incorrect username.";
            //echo "<p>Error: " . $error . "</p>";
        }
    }

    mysqli_close($conn);
    //echo "<p>Database connection closed.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | TapServe</title>
    <link rel="stylesheet" type="text/css" href="../asset/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h2>Login to TapServe</h2>
        <?php if ($error != "") { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Username" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Password" required><br><br>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>