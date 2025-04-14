<?php
/**
 * Script d'inscription sécurisé
 * Ce script démontre l'utilisation de password_hash() pour sécuriser les mots de passe
 */
session_start();
require_once '../config/database.php';

// Vérification que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation basique des données
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères";
    }
    
    if (empty($email)) {
        $errors[] = "L'adresse e-mail est requise";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'adresse e-mail invalide";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    // Vérifier si l'utilisateur ou l'e-mail existe déjà
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $errors[] = "Ce nom d'utilisateur ou cette adresse e-mail est déjà utilisé(e)";
        }
    }
    
    // Si pas d'erreurs, procéder à l'inscription
    if (empty($errors)) {
        // IMPORTANT: Utilisation de password_hash() pour sécuriser le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $result = $stmt->execute([$username, $email, $hashed_password]);
            
            if ($result) {
                $_SESSION['success'] = "Inscription réussie! Vous pouvez maintenant vous connecter.";
                header('Location: index.php');
                exit;
            } else {
                $errors[] = "Erreur lors de l'inscription. Veuillez réessayer.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données: " . $e->getMessage();
        }
    }
    
    // Si des erreurs sont survenues, les stocker en session
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = [
            'username' => $username,
            'email' => $email
        ];
        header('Location: register.php');
        exit;
    }
}
?>