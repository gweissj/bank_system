<?php
class Client {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO Clients (First_name, Last_name, Patronymic, Date_of_birth, Phone_number, Passport_number, Email, Gender, Password)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisss", 
            $data['first_name'], 
            $data['last_name'], 
            $data['patronymic'], 
            $data['date_of_birth'], 
            $data['phone_number'], 
            $data['passport_number'], 
            $data['email'], 
            $data['gender'], 
            $hashed_password
        );
        return $stmt->execute();
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM Clients WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                return $user['ID_client'];
            }
        }
        return false;
    }
}
?>