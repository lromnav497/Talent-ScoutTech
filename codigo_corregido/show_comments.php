<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">
    <title>Práctica RA3 - Comments editor</title>
</head>
<body>
<header>
    <h1>Comments editor</h1>
</header>
<main class="player">

<?php
require_once dirname(__FILE__) . '/private/conf.php';

# Require logged users
require dirname(__FILE__) . '/private/auth.php';

# List comments
if (isset($_GET['id'])) {
	// Validate and filter ID to prevent SQLi
	$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
	if (!$id || $id <= 0) {
		die("ID inválido");
	}

	// Use prepared statement to prevent SQLi
	$stmt = $db->prepare("SELECT commentId, username, body FROM comments C, users U WHERE C.playerId = :id AND U.userId = C.userId ORDER BY C.playerId DESC");
	$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
	$result = $stmt->execute() or die("Invalid query");

	while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
		// Escape output to prevent XSS
		$username_safe = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
		$body_safe = htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8');
		
		echo "<div>
                <h4>" . $username_safe . "</h4> 
                <p>commented: " . $body_safe . "</p>
              </div>";
	}

	$playerId = $id;
}

# Show form

?>

<div>
    <a href="list_players.php">Back to list</a>
    <a class="black" href="add_comment.php?id=<?php echo htmlspecialchars($playerId ?? ''); ?>"> Add comment</a>
</div>

</main>
<footer class="listado">
    <img src="images/logo-iesra-cadiz-color-blanco.png">
    <h4>Puesta en producción segura</h4>
    < Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >
</footer>
</body>
</html>
