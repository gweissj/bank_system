<?php
class Account {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    //получение всех активных счетов пользователя
    public function getAllByClientId($client_id) {
        $sql = "SELECT ID_accounts, Account_type, Balance, Currency, Opened_date 
                FROM Accounts 
                WHERE ID_client=? AND Active_account=1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }

        return $accounts;
    }

    //создание нового счета
    public function create($client_id, $account_type, $currency, $initial_balance) {
        $sql = "INSERT INTO Accounts (ID_client, Account_type, Balance, Opened_date, Active_account, Currency) 
                VALUES (?, ?, ?, NOW(), 1, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isds", $client_id, $account_type, $initial_balance, $currency);

        return $stmt->execute();
    }

    //получение баланса счета по его ID
    public function getBalanceById($account_id) {
        $sql = "SELECT Balance FROM Accounts WHERE ID_accounts=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['Balance'];
        }

        return null;
    }

    //получение ID активного счета клиента
    public function getActiveAccountIdByClientId($client_id) {
        $sql = "SELECT ID_accounts FROM Accounts WHERE ID_client=? AND Active_account=1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['ID_accounts'];
        }

        return null; 
    }

    //обновление баланса счета
    public function updateBalance($account_id, $new_balance) {
        $sql = "UPDATE Accounts SET Balance=? WHERE ID_accounts=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $new_balance, $account_id);

        return $stmt->execute();
    }
}
?>