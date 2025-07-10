<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Goal.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $amount = $_POST['target_amount'];
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    $goalModel = new Goal($pdo);
    $goalModel->create($_SESSION['user_id'], $name, $amount, $deadline);

    header("Location: ../dashboard.php");
    exit();
}

