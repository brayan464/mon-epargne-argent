<?php
class User {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getByEmailOrPhone($email, $phone) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        return $stmt->fetch();
    }

    public function register($email, $phone, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, phone, password) VALUES (?, ?, ?)");
        $stmt->execute([$email, $phone, $hash]);
        return $this->db->lastInsertId();
    }
}

