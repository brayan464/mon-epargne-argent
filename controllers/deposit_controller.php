<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Deposit.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['goal_id'])) {
    header("Location: ../login.php");
    exit();
}

$goal_id = $_POST['goal_id'];
$amount = floatval($_POST['amount']);

$depositModel = new Deposit($pdo);
$depositModel->add($goal_id, $amount);

header("Location: ../dashboard.php");
exit();

