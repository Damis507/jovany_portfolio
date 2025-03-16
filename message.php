<?php
require_once('config/config.php');

// Fonction pour nettoyer les entrées utilisateur
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialiser un tableau pour les erreurs
    $errors = [];
    
    // Vérifier et traiter les données du formulaire
    if (empty($_POST['nom'])) {
        $errors[] = "Le nom est requis";
    } else {
        $nom = cleanInput($_POST['nom']);
        // Vérifier la longueur du nom
        if (strlen($nom) > 50) {
            $errors[] = "Le nom ne doit pas dépasser 50 caractères";
        }
    }
    
    if (empty($_POST['email'])) {
        $errors[] = "L'email est requis";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        // Vérifier si l'email est valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide";
        }
    }
    
    if (empty($_POST['message'])) {
        $errors[] = "Le message est requis";
    } else {
        $message = cleanInput($_POST['message']);
    }
    
    // Date d'envoi actuelle
    $date_envoi = date("Y-m-d H:i:s");
    
    // Statut par défaut
    $statut = "non traité";

    // Si aucune erreur, procéder à l'insertion
    if (empty($errors)) {
        try {
            // Connexion à la base de données
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Insérer les données dans la base avec une requête préparée
            $stmt = $pdo->prepare("INSERT INTO messages (nom, email, message, date_envoi, statut) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $email, $message, $date_envoi, $statut]);
            
            // Message de succès
            $success = "Merci pour votre message ! Nous vous contacterons bientôt.";
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoi de message</title>
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="success">
            <?php echo $success; ?>
        </div>
    <?php else: ?>
        <form action="message.php" method="POST" enctype="multipart/form-data">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?php echo isset($nom) ? $nom : ''; ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="4" required><?php echo isset($message) ? $message : ''; ?></textarea>

            <button type="submit">Envoyer</button>
        </form>
    <?php endif; ?>
</body>
</html>