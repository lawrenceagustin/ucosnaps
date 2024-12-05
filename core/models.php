<?php  

require_once 'dbConfig.php';

function checkIfUserExists($pdo, $username) {
	$response = array();
	$sql = "SELECT * FROM user_accounts WHERE username = ?";
	$stmt = $pdo->prepare($sql);

	if ($stmt->execute([$username])) {

		$userInfoArray = $stmt->fetch();

		if ($stmt->rowCount() > 0) {
			$response = array(
				"result"=> true,
				"status" => "200",
				"userInfoArray" => $userInfoArray
			);
		}

		else {
			$response = array(
				"result"=> false,
				"status" => "400",
				"message"=> "User doesn't exist from the database"
			);
		}
	}

	return $response;

}

function insertNewUser($pdo, $username, $first_name, $last_name, $password) {
	$response = array();
	$checkIfUserExists = checkIfUserExists($pdo, $username); 

	if (!$checkIfUserExists['result']) {

		$sql = "INSERT INTO user_accounts (username, first_name, last_name, password) 
		VALUES (?,?,?,?)";

		$stmt = $pdo->prepare($sql);

		if ($stmt->execute([$username, $first_name, $last_name, $password])) {
			$response = array(
				"status" => "200",
				"message" => "User successfully inserted!"
			);
		}

		else {
			$response = array(
				"status" => "400",
				"message" => "An error occured with the query!"
			);
		}
	}

	else {
		$response = array(
			"status" => "400",
			"message" => "User already exists!"
		);
	}

	return $response;
}

function getAllUsers($pdo) {
	$sql = "SELECT * FROM user_accounts";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute();

	if ($executeQuery) {
		return $stmt->fetchAll();
	}
}

function getUserByID($pdo, $username) {
	$sql = "SELECT * FROM user_accounts WHERE username = ?";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$username]);

	if ($executeQuery) {
		return $stmt->fetch();
	}
}

function insertPhoto($pdo, $photo_name, $username, $description, $album_id = NULL, $photo_id = NULL) {

  if (empty($photo_id)) {
      $sql = "INSERT INTO photos (photo_name, username, description, album_id) VALUES(?,?,?,?)";
      $stmt = $pdo->prepare($sql);
      $executeQuery = $stmt->execute([$photo_name, $username, $description, $album_id]);
      
      if ($executeQuery) {
          return true;
      }
  } else {
      $sql = "UPDATE photos SET photo_name = ?, description = ?, album_id = ? WHERE photo_id = ?";
      $stmt = $pdo->prepare($sql);
      $executeQuery = $stmt->execute([$photo_name, $description, $album_id, $photo_id]);

      if ($executeQuery) {
          return true;
      }
  }
  return false;
}



  function getAllPhotos($pdo) {
    $sql = "SELECT * FROM photos ORDER BY date_added DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function getPhotoByID($pdo, $photo_id) {
	$sql = "SELECT * FROM photos WHERE photo_id = ?";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$photo_id]);

	if ($executeQuery) {
		return $stmt->fetch();
	}
}


function deletePhoto($pdo, $photo_id) {
	$sql = "DELETE FROM photos WHERE photo_id  = ?";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$photo_id]);

	if ($executeQuery) {
		return true;
	}
	
}

function insertComment($pdo, $photo_id, $username, $description) {
	$sql = "INSERT INTO photos (photo_id, username, description) VALUES(?,?,?)";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$photo_id, $username, $description]);

	if ($executeQuery) {
		return true;
	}
}

function getCommentByID($pdo, $comment_id) {
	$sql = "SELECT * FROM comments WHERE comment_id = ?";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$comment_id]);

	if ($executeQuery) {
		return $stmt->fetch();
	}
}


function updateComment($pdo, $description, $comment_id) {
	$sql = "UPDATE comments SET description = ?, WHERE comment_id = ?";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$description, $comment_id,]);

	if ($executeQuery) {
		return true;
	}
}

function deleteComment($pdo, $comment_id) {
	$sql = "DELETE FROM comments WHERE comment_id = ?";
	$stmt = $pdo->prepare($sql);
	$executeQuery = $stmt->execute([$comment_id]);

	if ($executeQuery) {
		return true;
	}
}

function getAllPhotosJson($pdo) {
	if (empty($username)) {
		$sql = "SELECT * FROM photos";
		$stmt = $pdo->prepare($sql);
		$executeQuery = $stmt->execute();

		if ($executeQuery) {
			return $stmt->fetchAll();
		}
	}
}

// create album
function createAlbum($albumName, $username) {
  $conn = new mysqli('localhost', 'root', '', 'your_database');
  $stmt = $conn->prepare("INSERT INTO albums (album_name, username) VALUES (?, ?)");
  $stmt->bind_param("ss", $albumName, $username);
  $stmt->execute();
  $stmt->close();
  $conn->close();
}

//insert photos into album
function addPhotoToAlbum($photoName, $username, $description, $albumId) {
  $conn = new mysqli('localhost', 'root', '', 'your_database');
  $stmt = $conn->prepare("INSERT INTO photos (photo_name, username, description, album_id) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("sssi", $photoName, $username, $description, $albumId);
  $stmt->execute();
  $stmt->close();
  $conn->close();
}

function getAlbumsWithPhotos($username) {
  $conn = new mysqli('localhost', 'root', '', 'your_database');
  $stmt = $conn->prepare("
      SELECT a.album_id, a.album_name, p.photo_id, p.photo_name, p.description 
      FROM albums a 
      LEFT JOIN photos p ON a.album_id = p.album_id 
      WHERE a.username = ?
  ");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  $albums = [];
  while ($row = $result->fetch_assoc()) {
      $albums[$row['album_id']]['album_name'] = $row['album_name'];
      $albums[$row['album_id']]['photos'][] = $row;
  }
  $stmt->close();
  $conn->close();
  return $albums;
}




