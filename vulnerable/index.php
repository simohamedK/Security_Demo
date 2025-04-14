<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application vulnérable - Connexion</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Application vulnérable</h1>
        
        <div class="nav">
            <a href="../">Retour à l'accueil</a>
            <a href="register.php">Inscription</a>
        </div>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            echo '<div class="alert alert-success">Vous êtes connecté en tant que ' . htmlspecialchars($_SESSION['username']) . '!</div>';
            echo '<p><a href="logout.php">Se déconnecter</a></p>';
        } else {
        ?>
        
        <h2>Connexion</h2>
        <p>Cette version est vulnérable aux attaques par force brute.</p>
        
        <form action="login_process.php" method="post">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        
        <?php } ?>
        
        <div class="alert alert-danger">
            <strong>Vulnérabilité :</strong> Cette version ne limite pas le nombre de tentatives de connexion
            et permet à un attaquant d'essayer un nombre illimité de mots de passe.
        </div>
    </div>
</body>
</html>