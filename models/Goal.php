
<?php
class Goal {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function create($user_id, $name, $amount, $deadline = null) {
        $stmt = $this->db->prepare("INSERT INTO savings_goals (user_id, name, target_amount, deadline) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $amount, $deadline]);
    }

    public function getAllByUser($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM savings_goals WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
}
