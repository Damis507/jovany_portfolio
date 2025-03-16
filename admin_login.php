
<?php
// Démarrer la session pour la gestion de l'authentification
session_start();

require_once('config/config.php');

// Redirection si déjà connecté
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_messages.php');
    exit; // Important : arrêter l'exécution après la redirection
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
   
    try {
        // Utiliser la connexion PDO de config.php
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $password === $user['password']) {
            // Authentification réussie
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            header('Location: admin_messages.php');
            exit; // Important : arrêter l'exécution après la redirection
        } else {
            $login_error = "Identifiants incorrects";
        }
    } catch(PDOException $e) {
        $login_error = "Erreur de connexion à la base de données: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration</title>
</head>
<body>
    <h1>Connexion Administration</h1>
    
    <?php if (!empty($login_error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($login_error); ?></p>
    <?php endif; ?>
    
    <form method="post" action="">
        <div>
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit" name="login">Se connecter</button>
        </div>
    </form>
</body>
</html>
