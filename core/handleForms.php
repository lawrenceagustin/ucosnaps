<?php  
require_once 'dbConfig.php';
require_once 'models.php';

if (isset($_POST['insertNewUserBtn'])) {
	$username = trim($_POST['username']);
	$first_name = trim($_POST['first_name']);
	$last_name = trim($_POST['last_name']);
	$password = trim($_POST['password']);
	$confirm_password = trim($_POST['confirm_password']);

	if (!empty($username) && !empty($first_name) && !empty($last_name) && !empty($password) && !empty($confirm_password)) {

		if ($password == $confirm_password) {

			$insertQuery = insertNewUser($pdo, $username, $first_name, $last_name, password_hash($password, PASSWORD_DEFAULT));
			$_SESSION['message'] = $insertQuery['message'];

			if ($insertQuery['status'] == '200') {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../login.php");
			}

			else {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../register.php");
			}

		}
		else {
			$_SESSION['message'] = "Please make sure both passwords are equal";
			$_SESSION['status'] = '400';
			header("Location: ../register.php");
		}

	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}
}

if (isset($_POST['loginUserBtn'])) {
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);

	if (!empty($username) && !empty($password)) {

		$loginQuery = checkIfUserExists($pdo, $username);
		$userIDFromDB = $loginQuery['userInfoArray']['user_id'];
		$usernameFromDB = $loginQuery['userInfoArray']['username'];
		$passwordFromDB = $loginQuery['userInfoArray']['password'];

		if (password_verify($password, $passwordFromDB)) {
			$_SESSION['user_id'] = $userIDFromDB;
			$_SESSION['username'] = $usernameFromDB;
			header("Location: ../index.php");
		}

		else {
			$_SESSION['message'] = "Username/password invalid";
			$_SESSION['status'] = "400";
			header("Location: ../login.php");
		}
	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}

}

if (isset($_GET['logoutUserBtn'])) {
	unset($_SESSION['user_id']);
	unset($_SESSION['username']);
	header("Location: ../login.php");
}


if (isset($_POST['insertPhotoBtn'])) {
    
  // Get Description
  $description = $_POST['photoDescription'];

  // Album selection
  $albumId = !empty($_POST['albumId']) ? $_POST['albumId'] : NULL; 
  
  // Get file name
  $fileName = $_FILES['image']['name'];

  // Get temporary file name
  $tempFileName = $_FILES['image']['tmp_name'];

  // Get file extension
  $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

  // Generate a random unique ID for the image name
  $uniqueID = sha1(md5(rand(1,9999999)));

  // Combine the unique ID with the file extension to generate the image name
  $imageName = $uniqueID . "." . $fileExtension;

  // Handle photo editing (optional)
  if (isset($_POST['photo_id'])) {
      $photo_id = $_POST['photo_id'];
  } else {
      $photo_id = NULL;  // NULL for new photos
  }

  // Insert the photo record into the database (including album_id)
  $saveImgToDb = insertPhoto($pdo, $imageName, $_SESSION['username'], $description, $albumId, $photo_id);

  // Store the actual image file to the images folder
  if ($saveImgToDb) {
      // Specify the folder where the image should be saved
      $folder = "../images/" . $imageName;

      // Move the uploaded file to the specified folder
      if (move_uploaded_file($tempFileName, $folder)) {
          header("Location: ../index.php"); // Redirect back to the main page
          exit;
      } else {
          echo "Error uploading the file.";
      }
  } else {
      echo "Error inserting photo into database.";
  }
}



if (isset($_POST['deletePhotoBtn'])) {
	$photo_name = $_POST['photo_name'];
	$photo_id = $_POST['photo_id'];
	$deletePhoto = deletePhoto($pdo, $photo_id);

	if ($deletePhoto) {
		unlink("../images/".$photo_name);
		header("Location: ../index.php");
	}

}

//album creation

if (isset($_POST['createAlbumBtn'])) {
  $albumName = trim($_POST['albumName']);
  $username = $_SESSION['username'];

  if (!empty($albumName)) {
      $stmt = $pdo->prepare("INSERT INTO albums (album_name, username) VALUES (?, ?)");
      $stmt->execute([$albumName, $username]);
      $_SESSION['message'] = "Album created successfully!";
      header("Location: ../index.php");
  } else {
      $_SESSION['message'] = "Album name cannot be empty!";
      header("Location: ../index.php");
  }
}

// Edit Album Name
if (isset($_POST['editAlbumBtn'])) {
  $newAlbumName = $_POST['newAlbumName'];
  $albumId = $_POST['album_id'];

  $stmt = $pdo->prepare("UPDATE albums SET album_name = ? WHERE album_id = ? AND username = ?");
  $stmt->execute([$newAlbumName, $albumId, $_SESSION['username']]);

  header("Location: ../viewAlbum.php?album_id=" . $albumId);
  exit;
}

// Delete Album
if (isset($_POST['deleteAlbumBtn'])) {
  $albumId = $_POST['album_id'];

  $stmt = $pdo->prepare("DELETE FROM photos WHERE album_id = ?");
  $stmt->execute([$albumId]);

  $stmt = $pdo->prepare("DELETE FROM albums WHERE album_id = ? AND username = ?");
  $stmt->execute([$albumId, $_SESSION['username']]);

  header("Location: ../index.php");
  exit;
}