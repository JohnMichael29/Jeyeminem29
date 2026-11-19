<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($email == "" || $username == "" || $password == "") {
        $error = "Please fill up all the fields.";
    } else if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";

        } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? OR username = ?");
        mysqli_stmt_bind_param($check, "ss", $email, $username);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Email or username is already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = mysqli_prepare($conn, "INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($sql, "sss", $email, $username, $hashed);

            if (mysqli_stmt_execute($sql)) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Something went wrong, please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registration | Saving Memories</title>

    <link rel="stylesheet" href="SavingMemories.css">
</head>

<body>

    <div class="auth">

        <div class="panel">

            <a href="index.php" class="brand">
                Saving Memories
            </a>

            <h1>CREATE ACCOUNT</h1>

            <p class="sub">
                Start saving your memories today.
            </p>

            <?php if ($error != "") { ?>
                <p class="msg error"><?php echo $error; ?></p>
            <?php } ?>

            <form class="registration-form" method="POST" action="registration.php">


                <div class="field">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                        required
                    >

                </div>

                <div class="field">

                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                        required
                    >

                </div>

                <div class="field">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        required
                    >

                </div>

                    <button type="submit" class="go">
                        CREATE ACCOUNT
                    </button>
            </form>

            <div class="links">
                <p>
                    Already have an account?
                    <a href="login.php">Log in.</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
