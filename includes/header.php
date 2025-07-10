<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MonÉpargne</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>💰 MonÉpargne</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <nav>
                <a href="dashboard.php">🏠 Tableau de bord</a> |
                <a href="add_goal.php">🎯 Nouvel objectif</a> |
                <a href="history.php">📜 Historique</a> |
                <a href="logout.php">🚪 Déconnexion</a>
            </nav>
        <?php endif; ?>
        <hr>
    </header>

