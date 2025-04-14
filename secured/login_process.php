<?php
session_start();
require_once '../config/database.php';

// Fonction pour journaliser les tentatives de connexion
function logLoginAttempt($username, $success, $ip_address) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, success, attempt_time) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$username, $ip_address, $success ? 1 : 0]);
}

// Récupérer l'adresse IP du client
$ip_address = $_SERVER['REMOTE_ADDR'];

// Vérifier si le compte est verrouillé
if (isset($_SESSION['locked_until']) && $_SESSION['locked_until'] > time()) {
    $_SESSION['error'] = "Compte temporairement verrouillé. Veuillez réessayer plus tard.";
    header('Location: index.php');
    exit;
}

// Récupérer les données du formulaire
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Vérifier si les champs sont remplis
if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Tous les champs sont obligatoires.";
    header('Location: index.php');
    exit;
}

// Vérifier le CAPTCHA si requis
if (isset($_SESSION['failed_attempts']) && $_SESSION['failed_attempts'] >= 3) {
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    if (empty($recaptcha_response)) {
        $_SESSION['error'] = "Veuillez compléter le CAPTCHA.";
        header('Location: index.php');
        exit;
    }
    
    // Vérification du CAPTCHA avec l'API Google
    $recaptcha_secret = '6LddkAUrAAAAAEZV7RXntBZKTNikCocO0gCYEj-5';
    $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret.'&response='.$recaptcha_response);
    $response_data = json_decode($verify_response);
    
    if (!$response_data->success) {
        $_SESSION['error'] = "Vérification CAPTCHA échouée. Veuillez réessayer.";
        header('Location: index.php');
        exit;
    }
}

// Rechercher l'utilisateur dans la base de données
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est verrouillé dans la base de données
if ($user && $user['locked_until'] !== null) {
    $locked_until = strtotime($user['locked_until']);
    
    if ($locked_until > time()) {
        $_SESSION['locked_until'] = $locked_until;
        $_SESSION['error'] = "Compte temporairement verrouillé. Veuillez réessayer plus tard.";
        header('Location: index.php');
        exit;
    }
}

// Introduire un délai progressif basé sur le nombre de tentatives échouées
if (isset($_SESSION['failed_attempts'])) {
    $delay = min($_SESSION['failed_attempts'] * 1, 5); // Max 5 secondes de délai
    sleep($delay);
}

// Vérifier si l'utilisateur existe et si le mot de passe est correct
if ($user && password_verify($password, $user['password'])) {
    // Réinitialiser les compteurs de tentatives échouées
    $_SESSION['failed_attempts'] = 0;
    unset($_SESSION['locked_until']);
    
    // Mettre à jour la base de données
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Journaliser la connexion réussie
    logLoginAttempt($username, true, $ip_address);
    
    // Connexion réussie
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['success'] = "Connexion réussie!";
} else {
    // Incrémenter le compteur de tentatives échouées
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 1;
    } else {
        $_SESSION['failed_attempts']++;
    }
    
    // Journaliser la tentative échouée
    logLoginAttempt($username, false, $ip_address);
    
    // Mettre à jour le compteur dans la base de données si l'utilisateur existe
    if ($user) {
        $failed_attempts = $user['failed_attempts'] + 1;
        
        // Verrouiller le compte après 5 tentatives échouées
        if ($failed_attempts >= 5) {
            $lock_duration = 15 * 60; // 15 minutes en secondes
            $locked_until = date('Y-m-d H:i:s', time() + $lock_duration);
            $_SESSION['locked_until'] = time() + $lock_duration;
            
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, last_attempt = NOW(), locked_until = ? WHERE id = ?");
            $stmt->execute([$failed_attempts, $locked_until, $user['id']]);
            
            $_SESSION['error'] = "Trop de tentatives échouées. Votre compte est verrouillé pour 15 minutes.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, last_attempt = NOW() WHERE id = ?");
            $stmt->execute([$failed_attempts, $user['id']]);
            
            $_SESSION['error'] = "Nom d'utilisateur ou mot de passe incorrect. Tentatives restantes avant verrouillage: " . (5 - $failed_attempts);
        }
    } else {
        $_SESSION['error'] = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}

header('Location: index.php');
exit;