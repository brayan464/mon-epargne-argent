<?php
$host = 'localhost';
$dbname = 'mon_epargne';
$user = 'root';
$pass = 'AZERTY237@br'; // à modifier selon ton environnement

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

