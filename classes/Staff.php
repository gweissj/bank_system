<?php
class Staff {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT ID_Staff, Password FROM Staff WHERE Email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                return $row['ID_Staff'];
            }
        }
        return false;
    }
    

    public function getStaffInfo($Staff_id) {
        $stmt = $this->conn->prepare("SELECT First_name, Last_name, Patronymic, Post FROM Staff WHERE ID_Staff = ?");
        $stmt->bind_param("i", $Staff_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function isCreditDepartmentStaff($Staff_id) {
        $stmt = $this->conn->prepare("SELECT Post FROM Staff WHERE ID_Staff = ?");
        $stmt->bind_param("i", $Staff_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['Post'] === 'сотрудник кредитного отдела';
        }
        return false;
    }

    public function isAdmin($Staff_id) {
        $sql = "SELECT Post FROM Staff WHERE ID_Staff = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $Staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['Post'] === 'администратор';
        }
        return false;
    }
}
?>
