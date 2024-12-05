<?php require_once 'core/dbConfig.php'; ?>
<?php require_once 'core/models.php'; ?>

<?php  
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Albums</title>
    <link rel="stylesheet" href="styles/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="createAlbumForm" style="margin: 20px;">
        <form action="core/handleForms.php" method="POST">
            <label for="albumName">Create New Album:</label>
            <input type="text" name="albumName" placeholder="Album Name" required>
            <button type="submit" name="createAlbumBtn">Create Album</button>
        </form>
    </div>

    <h2>Your Albums</h2>

    <?php
    $stmt = $pdo->prepare("SELECT album_id, album_name FROM albums WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $albums = $stmt->fetchAll();

    foreach ($albums as $album) {
        echo "<h3><a href='viewAlbum.php?album_id={$album['album_id']}'>" . htmlspecialchars($album['album_name']) . "</a></h3>";
    }
    ?>



    <div class="insertPhotoForm" style="display: flex; justify-content: center;">
        <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
            <p>
                <label for="#">Description</label>
                <input type="text" name="photoDescription">
            </p>
            <p>
                <label for="albumId">Select Album</label>
                <select name="albumId">
                    <option value="">No Album</option>
                    <?php
                    $stmt = $pdo->prepare("SELECT album_id, album_name FROM albums WHERE username = ?");
                    $stmt->execute([$_SESSION['username']]);
                    $albums = $stmt->fetchAll();
                    foreach ($albums as $album) {
                        echo "<option value='{$album['album_id']}'>{$album['album_name']}</option>";
                    }
                    ?>
                </select>
            </p>
            <p>
                <label for="#">Photo Upload</label>
                <input type="file" name="image" required>
                <input type="submit" name="insertPhotoBtn" style="margin-top: 10px;">
            </p>
        </form>
    </div>
</body>
</html>
