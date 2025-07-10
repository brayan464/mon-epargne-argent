<?php
require_once "config/session.php";
require_once "config/db.php";
require_once "includes/functions.php";


$user_id = $_SESSION['user_id'];

// Récupérer les objectifs
$stmt = $pdo->prepare("SELECT * FROM savings_goals WHERE user_id = ?");
$stmt->execute([$user_id]);
$goals = $stmt->fetchAll();

$total_saved = 0;
$notifications = [];

// Calcul du montant épargné pour chaque objectif
foreach ($goals as &$goal) {
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM deposits WHERE goal_id = ?");
    $stmt->execute([$goal['id']]);
    $saved = $stmt->fetchColumn();
    $goal['saved'] = $saved ?: 0;
    $total_saved += $goal['saved'];
    
    // Calcul de la progression
    $progress = getProgress($goal['saved'], $goal['target_amount']);
    
    // Vérification des notifications et envoi d'emails
    if ($progress >= 100 && empty($goal['achievement_email_sent'])) {
        // Objectif atteint à 100%
        $notifications[] = [
            'type' => 'success',
            'message' => "Félicitations ! Vous avez atteint votre objectif '{$goal['name']}' !"
        ];
        
        // Envoi d'email
        if (isset($_SESSION['user_email'])) {
            require_once "includes/email_functions.php";
            sendGoalProgressEmail($_SESSION['user_email'], $goal['name'], $progress, $goal['target_amount'], $goal['saved']);
            
            // Marquer comme envoyé dans la base de données
            $stmt = $pdo->prepare("UPDATE savings_goals SET achievement_email_sent = 1, achievement_email_date = NOW() WHERE id = ?");
            $stmt->execute([$goal['id']]);
            $goal['achievement_email_sent'] = 1;
        }
    } 
    elseif ($progress >= 80 && empty($goal['progress80_email_sent'])) {
        // Objectif à 80%
        $notifications[] = [
            'type' => 'info',
            'message' => "Vous êtes à {$progress}% de votre objectif '{$goal['name']}' ! Continuez comme ça !"
        ];
        
        // Envoi d'email
        if (isset($_SESSION['user_email'])) {
            require_once "includes/email_functions.php";
            sendGoalProgressEmail($_SESSION['user_email'], $goal['name'], $progress, $goal['target_amount'], $goal['saved']);
            
            // Marquer comme envoyé dans la base de données
            $stmt = $pdo->prepare("UPDATE savings_goals SET progress80_email_sent = 1, progress80_email_date = NOW() WHERE id = ?");
            $stmt->execute([$goal['id']]);
            $goal['progress80_email_sent'] = 1;
        }
    }
}
unset($goal);

// Vérification de l'inactivité
$stmt = $pdo->prepare("
    SELECT MAX(d.created_at) as last_deposit 
    FROM deposits d 
    INNER JOIN savings_goals g ON g.id = d.goal_id 
    WHERE g.user_id = ?
");
$stmt->execute([$user_id]);
$last_deposit = $stmt->fetchColumn();

if ($last_deposit) {
    $last_deposit_date = new DateTime($last_deposit);
    $today = new DateTime();
    $interval = $today->diff($last_deposit_date);
    
    if ($interval->days >= 7) {
        $notifications[] = [
            'type' => 'warning',
            'message' => "Vous n'avez effectué aucun dépôt depuis {$interval->days} jours. Pensez à épargner régulièrement !"
        ];
        
        // Envoi d'email d'inactivité (une fois par semaine)
        $last_inactivity_email = $_SESSION['last_inactivity_email'] ?? null;
        if (isset($_SESSION['user_email']) && (!$last_inactivity_email || (new DateTime())->diff(new DateTime($last_inactivity_email))->days >= 7)) {
            require_once "includes/email_functions.php";
            prepareInactivityEmail($_SESSION['user_email'], $interval->days);
            $_SESSION['last_inactivity_email'] = (new DateTime())->format('Y-m-d H:i:s');
        }
    }
}

$goal_count = count($goals);

// Récupération des transactions
$stmt = $pdo->prepare("
    SELECT d.amount, d.created_at as deposit_date, g.name as goal_name
    FROM deposits d 
    INNER JOIN savings_goals g ON g.id = d.goal_id 
    WHERE g.user_id = ? 
    ORDER BY d.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Épargne - Tableau de bord</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f7fa; }
        .card-box {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .progress-bar { background-color: #22c55e; }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .notification {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .goal-card {
            transition: transform 0.3s;
        }
        .goal-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">💰 Mon Épargne</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a href="#" class="nav-link active">🏠 Accueil</a></li>
            <?php if (!empty($goals)): ?>
                <li class="nav-item">
                    <a href="deposit.php?goal_id=<?= $goals[0]['id'] ?>" class="nav-link">➕ Ajouter un dépôt</a>
                </li>
            <?php endif; ?>
            <li class="nav-item"><a href="add_goal.php" class="nav-link">🎯 Nouvel objectif</a></li>
            <li class="nav-item"><a href="logout.php" class="btn btn-outline-primary ms-2">Déconnexion</a></li>
        </ul>
    </div>
</nav>

<div class="container py-4">
    <?php if (!empty($notifications)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <?php foreach ($notifications as $notification): ?>
                    <div class="alert alert-<?= $notification['type'] ?> notification alert-dismissible fade show" role="alert">
                        <?= $notification['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-box text-center">
                <h6 class="text-muted">💼 Solde total</h6>
                <h2><?= formatMontant($total_saved) ?></h2>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-box text-center">
                <h6 class="text-muted">🎯 Objectifs</h6>
                <h2><?= $goal_count ?></h2>
            </div>
        </div>

        <?php foreach ($goals as $goal): 
            $progress = getProgress($goal['saved'], $goal['target_amount']);
            $progress_class = $progress >= 100 ? 'bg-success' : ($progress >= 80 ? 'bg-info' : 'bg-primary');
        ?>
        <div class="col-md-6">
            <div class="card-box goal-card">
                <h5><?= htmlspecialchars($goal['name']) ?></h5>
                <p class="mb-1 text-muted">Objectif : <?= formatMontant($goal['target_amount']) ?></p>
                <div class="progress mb-2" style="height: 10px;">
                    <div class="progress-bar <?= $progress_class ?>" style="width: <?= $progress ?>%" 
                         role="progressbar" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small><?= $progress ?>% atteint — <?= formatMontant($goal['saved']) ?> épargné</small>
                <div class="mt-2 d-flex justify-content-between">
                    <a href="deposit.php?goal_id=<?= $goal['id'] ?>" class="btn btn-sm btn-success">💸 Ajouter un dépôt</a>
                    <a href="goal_details.php?id=<?= $goal['id'] ?>" class="btn btn-sm btn-outline-primary">📊 Détails</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="col-12">
            <div class="card-box">
                <h6>📊 Répartition de l'épargne</h6>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card-box">
                <h6>📋 Historique des dépôts récents</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Objectif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $row): ?>
                                <tr>
                                    <td><?= date("d/m/Y", strtotime($row['deposit_date'])) ?></td>
                                    <td><?= formatMontant($row['amount']) ?></td>
                                    <td><?= htmlspecialchars($row['goal_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted">Aucune transaction</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const pieData = {
    labels: [
        <?php foreach ($goals as $goal): ?>
            '<?= htmlspecialchars($goal['name']) ?>',
        <?php endforeach; ?>
    ],
    datasets: [{
        data: [
            <?php foreach ($goals as $goal): ?>
                <?= $goal['saved'] ?>,
            <?php endforeach; ?>
        ],
        backgroundColor: [
            '#3b82f6', '#10b981', '#f59e0b', 
            '#ef4444', '#8b5cf6', '#ec4899'
        ],
        borderWidth: 1
    }]
};

new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: pieData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.label}: ${context.raw} FCFA`;
                    }
                }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>