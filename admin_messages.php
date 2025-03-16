<?php
// Démarrer la session pour la gestion de l'authentification
session_start();

require_once('config/config.php');

// Fonction de vérification de l'authentification
function isAuthenticated() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Vérification de l'authentification
if (!isAuthenticated()) {
    header('Location: admin_login.php');
    exit; // Important : arrêter l'exécution après la redirection
}

// Traitement de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit; // Important : arrêter l'exécution après la redirection
}

// Traitement du marquage d'un message comme traité
if (isset($_POST['marquer_traite']) && isset($_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);
    
    try {
        $stmt = $pdo->prepare("UPDATE messages SET statut = 'traité' WHERE id = ?");
        $stmt->execute([$message_id]);
        
        // Redirection pour éviter la resoumission du formulaire
        header("Location: admin_messages.php");
        exit; // Important : arrêter l'exécution après la redirection
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

// Déterminer le mode d'affichage (tous les messages ou seulement non traités)
$voir_tout = isset($_GET['voir_tout']) ? true : false;

// Préparer la requête SQL selon le mode d'affichage
if ($voir_tout) {
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY date_envoi DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE statut = ? ORDER BY date_envoi DESC");
    $stmt->execute(['non traité']);
}

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des messages</title>
</head>
<body>
    <h1>Administration des messages</h1>
    
    <div>
        <p>Connecté en tant que <?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="admin_messages.php?logout=1">Déconnexion</a></p>
        
        <?php if ($voir_tout): ?>
            <a href="admin_messages.php">Voir uniquement les messages non traités</a>
        <?php else: ?>
            <a href="admin_messages.php?voir_tout=1">Voir tous les messages</a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    
    <?php if (count($messages) > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date d'envoi</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?php echo $message['id']; ?></td>
                        <td><?php echo htmlspecialchars($message['nom']); ?></td>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($message['message'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($message['date_envoi'])); ?></td>
                        <td><?php echo htmlspecialchars($message['statut']); ?></td>
                        <td>
                            <?php if ($message['statut'] === 'non traité'): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <button type="submit" name="marquer_traite">Marquer comme traité</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun message <?php echo $voir_tout ? '' : 'non traité'; ?> trouvé.</p>
    <?php endif; ?>
</body>
</html>
