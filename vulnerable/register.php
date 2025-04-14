<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Démo Sécurité</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        
        <div class="nav">
            <a href="../">Accueil</a> | 
            <a href="login.php">Connexion</a>
        </div>
        
        <?php
        // Afficher les erreurs s'il y en a
        if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
            echo '<div class="alert alert-danger">';
            echo '<ul>';
            foreach ($_SESSION['errors'] as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
            unset($_SESSION['errors']);
        }
        
        // Récupérer les données du formulaire précédent en cas d'erreur
        $form_data = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        ?>
        
        <form action="register_process.php" method="post">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="email">Adresse e-mail :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
                <small>Le mot de passe doit contenir au moins 8 caractères.</small>
            </div>
            <div>
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>
        
        <div class="alert alert-info">
            <h3>Bonnes pratiques pour la sécurisation des mots de passe</h3>
            <ul>
                <li>Utilisez <code>password_hash()</code> pour hacher les mots de passe (avec l'algorithme bcrypt par défaut)</li>
                <li>Utilisez <code>password_verify()</code> pour vérifier les mots de passe lors de la connexion</li>
                <li>N'stockez jamais les mots de passe en texte brut dans la base de données</li>
                <li>N'utilisez pas des fonctions obsolètes comme MD5 ou SHA1 pour les mots de passe</li>
                <li>Exigez des mots de passe suffisamment longs et complexes</li>
            </ul>
        </div>
    </div>
</body>
</html>