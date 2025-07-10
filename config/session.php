<?php
// Démarre la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optionnel : Redirection si l’utilisateur n’est pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
