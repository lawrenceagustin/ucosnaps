<?php 
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

if (!isset($_GET['album_id'])) {
    echo "Album not found.";
    exit;
}

$album_id = $_GET['album_id'];

$stmt = $pdo->prepare("SELECT album_name FROM albums WHERE album_id = ? AND username = ?");
$stmt->execute([$album_id, $_SESSION['username']]);
$album = $stmt->fetch();

if (!$album) {
    echo "Album not found or you do not have permission to view it.";
    exit;
}

$album_name = $album['album_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album: <?php echo htmlspecialchars($album_name); ?></title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h2>Album: <?php echo htmlspecialchars($album_name); ?></h2>

    <!-- Edit Album Name Form -->
    <form action="core/handleForms.php" method="POST" style="margin-bottom: 20px;">
        <label for="newAlbumName">Edit Album Name:</label>
        <input type="text" name="newAlbumName" value="<?php echo htmlspecialchars($album_name); ?>" required>
        <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
        <button type="submit" name="editAlbumBtn">Save Changes</button>
    </form>

    <!-- Delete Album Form -->
    <form action="core/handleForms.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this album and all its photos?');">
        <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
        <button type="submit" name="deleteAlbumBtn" style="color: red;">Delete Album</button>
    </form> <br>

    <!-- Display photos from this album -->
    <?php
    $stmt = $pdo->prepare("SELECT * FROM photos WHERE album_id = ? ORDER BY date_added DESC");
    $stmt->execute([$album_id]);
    $photos = $stmt->fetchAll();

    if (count($photos) == 0) {
        echo "<p>No photos in this album.</p>";
    }

    foreach ($photos as $photo) {
        echo "
            <div class='photoContainer' style='background-color: ghostwhite; border-style: solid; border-color: gray;width: 50%; margin-bottom: 20px;'>
                <img src='images/{$photo['photo_name']}' alt='' style='width: 100%;'>
                <div class='photoDescription' style='padding:25px;'>
                    <h2>{$photo['username']}</h2>
                    <p><i>{$photo['date_added']}</i></p>
                    <h4>{$photo['description']}</h4>
                </div>
            </div>
        ";
    }
    ?>
</body>
</html>
