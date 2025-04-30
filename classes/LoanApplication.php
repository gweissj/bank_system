<?php
class LoanApplication {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    //полученик всех заявок
    public function getAllByClientId($client_id) {
        $sql = "SELECT * FROM Loan_Applications WHERE ID_client = ? ORDER BY Date_applied DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $applications = [];
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }

        return $applications;
    }

    //удаление заявки
    public function deleteById($application_id, $client_id) {
        $sql = "DELETE FROM Loan_Applications WHERE ID_application = ? AND ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $application_id, $client_id);
        return $stmt->execute();
    }

    //новая заявка
    public function create($client_id, $type_loan, $amount_loan) {
        $status = 'в стадии рассмотрения';
        $sql = "INSERT INTO Loan_Applications (ID_client, Type_loan, Amount_loan, Status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isds", $client_id, $type_loan, $amount_loan, $status);
        return $stmt->execute();
    }

    public function getById($application_id, $client_id) {
        $sql = "SELECT * FROM Loan_Applications WHERE ID_application = ? AND ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $application_id, $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function update($application_id, $client_id, $type_loan, $amount_loan) {
        $sql = "UPDATE Loan_Applications SET Type_loan = ?, Amount_loan = ? WHERE ID_application = ? AND ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdii", $type_loan, $amount_loan, $application_id, $client_id);
        return $stmt->execute();
    }
}
?>
