<?php
class Loan {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    //создание нового кредита
    public function createLoan($data) {
        $stmt = $this->conn->prepare("INSERT INTO Loans (ID_account, Amount_loan, Interest_rate, Type_loan, Date_open_loan, Date_close_loan, Monthly_payment, Having_loan)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "idssssdi",
            $data['ID_account'],
            $data['Amount_loan'],
            $data['Interest_rate'],
            $data['Type_loan'],
            $data['Date_open_loan'],
            $data['Date_close_loan'],
            $data['Monthly_payment'],
            $data['Having_loan']
        );
        return $stmt->execute();
    }

    //получение всех кредитов
    public function getAllByClientId($client_id) {
        $sql = "SELECT * FROM Loans WHERE ID_client = ? AND Having_loan = 1 ORDER BY Date_open_loan DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $loans = [];
        while ($row = $result->fetch_assoc()) {
            $loans[] = $row;
        }
        return $loans;
    }

    //закрытие кредита
    public function closeLoan($loan_id) {
        $sql = "UPDATE Loans SET Having_loan = 0 WHERE ID_loan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $loan_id);
        return $stmt->execute();
    }

    //информации о конкретном кредите
    public function getLoanById($loan_id) {
        $sql = "SELECT * FROM Loans WHERE ID_loan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function requestClosure($loan_id, $client_id) {
        $sql = "INSERT INTO Loan_Closure_Requests (ID_loan, ID_client) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $loan_id, $client_id);
        return $stmt->execute();
    }

    //закрытие кредита для клиента
    public function getClosureRequestsByClientId($client_id) {
        $sql = "SELECT lcr.ID_closure_request, l.Type_loan, lcr.Status 
                FROM Loan_Closure_Requests lcr
                JOIN Loans l ON lcr.ID_loan = l.ID_loan
                WHERE lcr.ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        return $requests;
    }
}
?>