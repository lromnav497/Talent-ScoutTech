<?php
require_once dirname(__FILE__) . '/private/conf.php';

# Require logged users (admin only)
require dirname(__FILE__) . '/private/auth.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Registro deshabilitado. Solo admins pueden registrar usuarios.');
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    // Sanitize input
    $username = trim(filter_var($_POST['username'], FILTER_SANITIZE_STRING));
    $password = $_POST['password'];
    
    // Validate length
    if (strlen($username) < 3 || strlen($password) < 6) {
        $error = "Username/Password muy corta (min 3/6 caracteres)";
    } else {
        // Check username exists
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = :user');
        $stmt->bindValue(':user', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $count = $result->fetchArray(SQLITE3_NUM)[0];
        
        if ($count > 0) {
            $error = "Usuario ya existe";
        } else {
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert with prepared statement
            $stmt = $db->prepare('INSERT INTO users (username, password, role) VALUES (:user, :pass, :role)');
            $stmt->bindValue(':user', $username, SQLITE3_TEXT);
            $stmt->bindValue(':pass', $hash, SQLITE3_TEXT);
            $stmt->bindValue(':role', 'user', SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                header("Location: list_players.php");
                exit;
            } else {
                $error = "Error al registrar usuario";
            }
        }
    }
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">
    <title>Práctica RA3 - Register user</title>
</head>
<body>
<header class="auth">
    <h1>Register user</h1>
</header>
<section class="auth">
    <?php if (isset($error)): ?>
    <div class="message" style="color: red;">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <section>
        <div>
            <h2>Register new user</h2>
            <form action="#" method="post">
                <label>Username</label>
                <input type="text" name="username" required minlength="3"><br>
                <label>Password</label>
                <input type="password" name="password" required minlength="6"><br>
                <input type="submit" value="Register">
            </form>
        </div>

        <div>
            <h2>Back</h2>
            <form action="#" method="post" class="menu-form">
                <a href="index.php">Back to home</a>
                <input type="submit" name="Logout" value="Logout" class="logout">
            </form>
        </div>
    </section>
</section>
<footer>
    <h4>Puesta en producción segura</h4>
    < Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >
</footer>
</body>
</html>
