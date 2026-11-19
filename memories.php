<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $category = $_POST["category"];
    $story = trim($_POST["story"]);

    $allowed = array("jpg", "jpeg", "png", "gif", "webp");

    if ($title == "") {
        $error = "Please put a title for your memory.";
    } else if (!isset($_FILES["image"]) || $_FILES["image"]["error"] != 0) {
        $error = "Please choose a picture to upload.";
    } else {
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Only jpg, jpeg, png, gif and webp are allowed.";
        } else if ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
            $error = "The picture is too big, max is 5MB.";
        } else {
            if (!is_dir("uploads")) {
                mkdir("uploads");
            }

            $filename = $user_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $filename)) {
                $sql = mysqli_prepare($conn, "INSERT INTO memories (user_id, title, category, story, image) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($sql, "issss", $user_id, $title, $category, $story, $filename);
                mysqli_stmt_execute($sql);
                $success = "Your memory has been saved!";
            } else {
                $error = "Upload failed, please try again.";
            }
        }
    }
}


if (isset($_GET["delete"])) {
    $id = $_GET["delete"];

    $get = mysqli_prepare($conn, "SELECT image FROM memories WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($get, "ii", $id, $user_id);
    mysqli_stmt_execute($get);
    $res = mysqli_stmt_get_result($get);
    $pic = mysqli_fetch_assoc($res);

    if ($pic) {
        if (file_exists("uploads/" . $pic["image"])) {
            unlink("uploads/" . $pic["image"]);
        }

        $del = mysqli_prepare($conn, "DELETE FROM memories WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($del, "ii", $id, $user_id);
        mysqli_stmt_execute($del);
    }

    header("Location: memories.php");
    exit();
}

$list = mysqli_prepare($conn, "SELECT * FROM memories WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($list, "i", $user_id);
mysqli_stmt_execute($list);
$memories = mysqli_stmt_get_result($list);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Memories | Saving Memories</title>

    <link rel="stylesheet" href="SavingMemories.css">
</head>

<body>
    <header class="top wrap">
        <div>
            <a href="index.php" class="logo"
            style="text-decoration: none;">
                Saving Memories
            </a>
            <p class="tag">
                Every moment deserves to be remembered.
            </p>
        </div>

        <nav aria-label="Main Navigation">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="memories.php" class="active">My Memories</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="wrap">

        <section>
            <span class="eyebrow">Welcome back</span>
            <h1>Hi, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <p>Add a new memory or look back on the ones you already saved.</p>
        </section>

        <section class="about-card">
            <h2>Add a Memory</h2>

            <?php if ($error != "") { ?>
                <p class="msg error"><?php echo $error; ?></p>
            <?php } ?>

            <?php if ($success != "") { ?>
                <p class="msg success"><?php echo $success; ?></p>
            <?php } ?>

            <form method="POST" action="memories.php" enctype="multipart/form-data">

                <div class="field">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Ex. My graduation day" required>
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="Achievements">Achievements</option>
                        <option value="Friendship">Friendship</option>
                        <option value="Family">Family</option>
                        <option value="Others">Others</option>
                    </select>
                </div>

                <div class="field">
                    <label for="story">Story behind it</label>
                    <textarea id="story" name="story" rows="4" placeholder="Write the story of this moment..."></textarea>
                </div>

                <div class="field">
                    <label for="image">Picture</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <button type="submit" class="btn">Save Memory</button>
            </form>
        </section>

        <section>
            <div class="center">
                <span class="eyebrow">Your Collection</span>
                <h2>My Saved Memories</h2>
            </div>

            <?php if (mysqli_num_rows($memories) == 0) { ?>
                <p class="center">You don't have any memories yet. Start adding one above!</p>
            <?php } else { ?>

                <div class="gallery">
                    <?php while ($row = mysqli_fetch_assoc($memories)) { ?>
                        <figure>
                            <img src="uploads/<?php echo htmlspecialchars($row["image"]); ?>"
                                alt="<?php echo htmlspecialchars($row["title"]); ?>">

                            <figcaption><?php echo htmlspecialchars($row["category"]); ?></figcaption>

                            <div class="memory-info">
                                <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($row["story"])); ?></p>
                                <small><?php echo date("F j, Y", strtotime($row["created_at"])); ?></small>
                                <a href="memories.php?delete=<?php echo $row["id"]; ?>"
                                    class="delete"
                                    onclick="return confirm('Delete this memory?');">Delete</a>
                            </div>
                        </figure>
                    <?php } ?>
                </div>

            <?php } ?>
        </section>

    </main>

    <footer>
        <p>Collect. Remember. Cherish.</p>

        <p>
            © 2026 Saving Memories. All moments matter.
        </p>
    </footer>

</body>
</html>
