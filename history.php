<?php
session_start();
require_once "config/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT d.*, g.name AS goal_name 
    FROM deposits d 
    JOIN savings_goals g ON d.goal_id = g.id 
    WHERE g.user_id = ?
    ORDER BY d.deposited_at DESC
");
$stmt->execute([$user_id]);
$deposits = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Historique - MonÉpargne</title>
    <style>
        /* Reset et styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 28px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        /* Lien de retour */
        .back-link {
            display: inline-block;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
            padding: 10px 15px;
            border-radius: 4px;
            border: 1px solid #3498db;
            transition: all 0.3s;
        }

        .back-link:hover {
            color: white;
            background-color: #3498db;
            text-decoration: none;
        }

        /* Tableau */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .history-table th {
            background-color: #3498db;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        .history-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .history-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Styles conditionnels pour le statut */
        .status-success {
            color: #27ae60;
            font-weight: 500;
        }

        .status-pending {
            color: #f39c12;
            font-weight: 500;
        }

        .status-failed {
            color: #e74c3c;
            font-weight: 500;
        }

        /* Montant */
        .amount {
            font-weight: bold;
            color: #2c3e50;
        }

        /* Date */
        .date {
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Méthode de paiement */
        .payment-method {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-method img {
            width: 24px;
            height: 24px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .history-table {
                display: block;
                overflow-x: auto;
            }
            
            .back-link {
                width: 100%;
                text-align: center;
                margin-bottom: 20px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .history-table tbody tr {
            animation: fadeIn 0.3s ease-out forwards;
            animation-delay: calc(var(--i) * 0.05s);
        }

        /* Message si vide */
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-size: 18px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <h2>Historique des dépôts</h2>
    <a href="dashboard.php" class="back-link">⬅ Retour au tableau de bord</a>

    <?php if (empty($deposits)): ?>
        <div class="empty-message">
            Aucun dépôt enregistré pour le moment.
        </div>
    <?php else: ?>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Objectif</th>
                    <th>Montant</th>
                    <th>Date</th>
                    <th>Méthode</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deposits as $i => $deposit): ?>
                <tr style="--i: <?= $i ?>">
                    <td><?= htmlspecialchars($deposit['goal_name']) ?></td>
                    <td class="amount"><?= number_format($deposit['amount'], 2) ?> FCFA</td>
                    <td class="date"><?= date('d/m/Y H:i', strtotime($deposit['deposited_at'])) ?></td>
                    <td>
                        <div class="payment-method">
                            <?php if ($deposit['payment_method'] === 'MTN MoMo'): ?>
                                <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="MTN MoMo">
                                <span>MTN MoMo</span>
                            <?php else: ?>
                                <?= $deposit['payment_method'] ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="status-<?= $deposit['status'] ?>">
                        <?= ucfirst($deposit['status']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>