<?php
require_once "config/session.php";
require_once "config/db.php";
require_once "includes/functions.php";

// Vérifier que l'ID de l'objectif est passé
if (!isset($_GET['goal_id'])) {
    header("Location: dashboard.php");
    exit();
}

$goal_id = intval($_GET['goal_id']);
$user_id = $_SESSION['user_id'];

// Vérifier que cet objectif appartient bien à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM savings_goals WHERE id = ? AND user_id = ?");
$stmt->execute([$goal_id, $user_id]);
$goal = $stmt->fetch();

if (!$goal) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);

    if ($amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO deposits (goal_id, amount) VALUES (?, ?)");
        $stmt->execute([$goal_id, $amount]);
        $message = "<div class='alert alert-success'>✅ Dépôt ajouté avec succès.</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Le montant doit être supérieur à 0.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un dépôt - MonÉpargne</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f5f7fa;
        }

        .container {
            max-width: 600px;
        }

        .form-box {
            background: #fff;
            padding: 30px;
            margin-top: 50px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand text-primary fw-bold" href="dashboard.php">💰 Mon Épargne</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link">🏠 Accueil</a></li>
            <li class="nav-item"><a href="add_goal.php" class="nav-link">🎯 Objectifs</a></li>
            <li class="nav-item"><a href="logout.php" class="btn btn-outline-primary ms-2">Se déconnecter</a></li>
        </ul>
    </div>
</nav>

<!-- Formulaire -->
<div class="container">
    <div class="form-box mt-4">
        <h4 class="mb-3 text-center">Ajouter un dépôt pour :</h4>
        <h5 class="text-center text-success"><?= htmlspecialchars($goal['name']) ?></h5>

        <?= $message ?>

        <form method="post">
            <div class="mb-3">
                <label for="amount" class="form-label">Montant à déposer</label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="1" required>
            </div>
            <button type="submit" class="btn btn-success w-100">💸 Ajouter</button>
        </form>

        <div class="text-center mt-3">
            <a href="dashboard.php" class="text-muted">⬅ Retour au tableau de bord</a>
        </div>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
