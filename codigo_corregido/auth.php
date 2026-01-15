<?php
require_once dirname(__FILE__) . '/conf.php';

session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict'
]);

$userId = FALSE;

// Check session timeout (1h)
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > 3600) {
        session_unset();
        session_destroy();
        session_start();
    }
}
$_SESSION['last_activity'] = time();

// Check whether a pair of user and password are valid; returns true if valid.
function areUserAndPasswordValid($user, $password) {
	global $db, $userId;

    // Use prepared statement to prevent SQLi
    $stmt = $db->prepare('SELECT userId, password FROM users WHERE username = :user');
    $stmt->bindValue(':user', $user, SQLITE3_TEXT);
	$result = $stmt->execute() or die("Invalid query");
	$row = $result->fetchArray(SQLITE3_ASSOC);

	if ($row && password_verify($password, $row['password'])) {
		session_regenerate_id(true);
		$userId = $row['userId'];
		$_SESSION['userId'] = $userId;
		$_SESSION['username'] = $user;
		return TRUE;
	}
	else
		return FALSE;
}

# On login
if (isset($_POST['username']) && isset($_POST['password'])) {		
	if (areUserAndPasswordValid($_POST['username'], $_POST['password'])) {
		header("Location: index.php");
		exit;
	} else {
		$error = "Invalid user or password.<br>";
	}
}

# On logout
if (isset($_POST['Logout'])) {
	session_unset();
	session_destroy();
	header("Location: index.php");
	exit;
}

# Check user and password
if (isset($_SESSION['userId'])) {
	$login_ok = TRUE;
	$error = "";
} else {
	$login_ok = FALSE;
	$error = "This page requires you to be logged in.<br>";
}

if ($login_ok == FALSE) {

?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="css/style.css">
        <title>Práctica RA3 - Authentication page</title>
    </head>
    <body>
    <header class="auth">
        <h1>Authentication page</h1>
    </header>
    <section class="auth">
        <div class="message">
            <?= $error ?>
        </div>
        <section>
            <div>
                <h2>Login</h2>
                <form action="#" method="post">
                    <label>User</label>
                    <input type="text" name="username" required><br>
                    <label>Password</label>
                    <input type="password" name="password" required><br>
                    <input type="submit" value="Login">
                </form>
            </div>

            <div>
                <h2>Logout</h2>
                <form action="#" method="post">
                    <input type="submit" name="Logout" value="Logout">
                </form>
            </div>
        </section>
    </section>
    <footer>
        <h4>Puesta en producción segura</h4>
        < Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >
    </footer>
    <?php
    exit (0);
}

?>
