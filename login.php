<?php
session_start();
include "db.php";

// if already logged in go straight to memories
if (isset($_SESSION["user_id"])) {
    header("Location: memories.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ?");
    mysqli_stmt_bind_param($sql, "s", $username);
    mysqli_stmt_execute($sql);
    $result = mysqli_stmt_get_result($sql);
    $row = mysqli_fetch_assoc($result);

    if ($row && password_verify($password, $row["password"])) {
        $_SESSION["user_id"] = $row["id"];
        $_SESSION["username"] = $row["username"];
        header("Location: memories.php");
        exit();
    } else {
        $error = "Wrong username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Saving Memories</title>

    <link rel="stylesheet" href="SavingMemories.css">
</head>

<body>

    <div class="auth">

        <div class="panel">

            <a href="index.html" class="brand">
                Saving Memories
            </a>

            <h1>LOG IN</h1>

            <p class="sub">
                Welcome back! Continue saving your memories.
            </p>

            <?php if (isset($_GET["registered"])) { ?>
                <p class="msg success">Account created! You can now log in.</p>
            <?php } ?>

            <?php if ($error != "") { ?>
                <p class="msg error"><?php echo $error; ?></p>
            <?php } ?>

            <form class="login-form" method="POST" action="login.php">

                <div class="field">

                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        required
                    >

                </div>


                <div class="field">

                    <label for="password">Password</label>

                    <div class="input-wrap">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle"
                            onclick="togglePassword()">
                            Show
                        </button>

                    </div>

                </div>

                <button type="submit" class="go">
                    LOGIN
                </button>

            </form>


            <div class="links">

                <p>
                    Don't have an account?
                    <a href="registration.php">Create account.</a>
                </p>

            </div>

        </div>

    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const button = document.querySelector(".toggle");

            if (password.type === "password") {
                password.type = "text";
                button.textContent = "Hide";
            } else {
                password.type = "password";
                button.textContent = "Show";
            }
        }
    </script>

</body>
</html>
