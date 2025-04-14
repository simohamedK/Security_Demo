<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application sécurisée - Connexion</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="container">
        <h1>Application sécurisée</h1>
        
        <div class="nav">
            <a href="../">Retour à l'accueil</a>
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
            // Vérifier si le compte est verrouillé
            if (isset($_SESSION['locked_until']) && $_SESSION['locked_until'] > time()) {
                $minutes_left = ceil(($_SESSION['locked_until'] - time()) / 60);
                echo '<div class="alert alert-danger">Compte temporairement verrouillé. Veuillez réessayer dans ' . $minutes_left . ' minute(s).</div>';
            } else {
        ?>
        
        <h2>Connexion</h2>
        <p>Cette version est protégée contre les attaques par force brute.</p>
        
        <form action="login_process.php" method="post">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>

            
            
            <?php
            // Afficher le CAPTCHA après 3 tentatives échouées
            if (isset($_SESSION['failed_attempts']) && $_SESSION['failed_attempts'] >= 3) {
                echo '<div class="g-recaptcha" data-sitekey="6LddkAUrAAAAAEZV7RXntBZKTNikCocO0gCYEj-5"></div>';
                echo '<p><small>CAPTCHA requis après plusieurs tentatives échouées</small></p>';
            }
            ?>
            
            
            <button type="submit">Se connecter</button>
        </form>
        
        <?php 
            }
        } 
        ?>
        
        <div class="alert alert-success">
            <strong>Protections implémentées :</strong>
            <ul>
                <li>Limitation du nombre de tentatives</li>
                <li>Verrouillage temporaire du compte après 5 tentatives échouées</li>
                <li>CAPTCHA après 3 tentatives échouées</li>
                <li>Délai progressif entre les tentatives</li>
                <li>Journalisation des tentatives de connexion échouées</li>
            </ul>
        </div>
    </div>
</body>
</html>