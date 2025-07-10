<?php
session_start();
require_once "config/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $amount = $_POST['target_amount'];
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    $stmt = $pdo->prepare("INSERT INTO savings_goals (user_id, name, target_amount, deadline) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $name, $amount, $deadline]);

    $message = "Objectif créé avec succès!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un objectif - MonÉpargne</title>
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
            max-width: 600px;
            margin: 0 auto;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 28px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        /* Styles du formulaire */
        form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        button {
            width: 100%;
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 500;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Message de succès */
        .success-message {
            color: #27ae60;
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #a5d6a7;
        }

        /* Lien de retour */
        .back-link {
            display: inline-block;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            form {
                padding: 20px;
            }
        }

        /* Étiquettes des champs */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <h2>Créer un objectif</h2>
    
    <?php if ($message): ?>
        <div class="success-message"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div>
            <label for="name">Nom de l'objectif</label>
            <input type="text" id="name" name="name" placeholder="Ex: Nouvelle voiture" required>
        </div>
        
        <div>
            <label for="target_amount">Montant cible (FCFA)</label>
            <input type="number" id="target_amount" name="target_amount" placeholder="Ex: 500000" step="0.01" min="0" required>
        </div>
        
        <div>
            <label for="deadline">Date limite (facultatif)</label>
            <input type="date" id="deadline" name="deadline">
        </div>
        
        <button type="submit">Créer l'objectif</button>
    </form>

    <a href="dashboard.php" class="back-link">⬅ Retour au tableau de bord</a>
</body>
</html>