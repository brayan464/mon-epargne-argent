<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];

        $userModel = new User($pdo);
        $user = $userModel->getByEmailOrPhone($email, $phone);

        if ($user) {
            $_SESSION['error'] = "Email ou numéro déjà utilisé.";
        } else {
            $user_id = $userModel->register($email, $phone, $password);
            $_SESSION['user_id'] = $user_id;
            header("Location: ../dashboard.php");
            exit();
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $emailOrPhone = $_POST['email_or_phone'];
        $password = $_POST['password'];

        $userModel = new User($pdo);
        $user = $userModel->getByEmailOrPhone($emailOrPhone, $emailOrPhone);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: ../dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Identifiants incorrects.";
        }
    }
}

header("Location: ../login.php");
exit();

