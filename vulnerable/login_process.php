<?php
session_start();
require_once '../config/database.php';

// Récupérer les données du formulaire
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Vérifier si les champs sont remplis
if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Tous les champs sont obligatoires.";
    header('Location: index.php');
    exit;
}

// Rechercher l'utilisateur dans la base de données
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe et si le mot de passe est correct
if ($user && password_verify($password, $user['password'])) {
    // Connexion réussie
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['success'] = "Connexion réussie!";
} else {
    // Connexion échouée
    $_SESSION['error'] = "Nom d'utilisateur ou mot de passe incorrect.";
}

header('Location: index.php');
exit;