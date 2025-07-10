<?php
class Deposit {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function add($goal_id, $amount, $method = 'MTN MoMo', $status = 'success') {
        $stmt = $this->db->prepare("INSERT INTO deposits (goal_id, amount, payment_method, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$goal_id, $amount, $method, $status]);
    }

    public function getTotalByGoal($goal_id) {
        $stmt = $this->db->prepare("SELECT SUM(amount) AS total FROM deposits WHERE goal_id = ?");
        $stmt->execute([$goal_id]);
        return $stmt->fetchColumn();
    }
}

