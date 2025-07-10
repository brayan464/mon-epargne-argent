<?php
// Ce fichier est appelé automatiquement par MTN après un paiement réussi

require_once "config/db.php";

// Récupérer le corps JSON envoyé par MTN
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Tu peux logger les données si nécessaire pour le debug
file_put_contents('callback_log.txt', date('Y-m-d H:i:s') . " - " . print_r($data, true), FILE_APPEND);

// Vérifie les champs essentiels
if (!empty($data['externalId']) && !empty($data['amount']) && !empty($data['status'])) {
    $goal_id = intval($data['externalId']); // l'externalId correspond à notre goal_id dans requestToPay
    $amount = floatval($data['amount']);
    $status = strtolower($data['status']);
    $referenceId = $data['financialTransactionId'] ?? uniqid('ref_');

    // Insère ou met à jour le dépôt
    $stmt = $pdo->prepare("INSERT INTO deposits (goal_id, amount, payment_method, momo_reference_id, status) VALUES (?, ?, 'MTN MoMo', ?, ?)");
    $stmt->execute([$goal_id, $amount, $referenceId, $status]);

    http_response_code(200); // OK
} else {
    http_response_code(400); // Bad request
}
?>

