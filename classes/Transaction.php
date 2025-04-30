<?php
class Transaction {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    //все транзакций клиента
    public function getAllByClientId($client_id) {
        $sql_accounts = "SELECT ID_accounts FROM Accounts WHERE ID_client=? AND Active_account=1";
        $stmt_accounts = $this->conn->prepare($sql_accounts);
        $stmt_accounts->bind_param("i", $client_id);
        $stmt_accounts->execute();
        $result_accounts = $stmt_accounts->get_result();

        $account_ids = [];
        while ($row = $result_accounts->fetch_assoc()) {
            $account_ids[] = $row['ID_accounts'];
        }

        if (empty($account_ids)) {
            return [];
        }

        $account_ids_str = implode(',', array_fill(0, count($account_ids), '?'));

        $sql_transactions = "SELECT * FROM Transactions 
                             WHERE ID_sending_account IN ($account_ids_str) 
                                OR ID_recipient_account IN ($account_ids_str) 
                             ORDER BY Date_transaction DESC";

        $types = str_repeat('i', count($account_ids));
        $stmt_transactions = $this->conn->prepare($sql_transactions);
        $stmt_transactions->bind_param($types . $types, ...array_merge($account_ids, $account_ids));
        $stmt_transactions->execute();
        $result_transactions = $stmt_transactions->get_result();

        $transactions = [];
        while ($row = $result_transactions->fetch_assoc()) {
            $transactions[] = $row;
        }

        return $transactions;
    }

    public function getClientName($account_id) {
        $sql_account = "SELECT ID_client FROM Accounts WHERE ID_accounts=?";
        $stmt_account = $this->conn->prepare($sql_account);
        $stmt_account->bind_param("i", $account_id);
        $stmt_account->execute();
        $result_account = $stmt_account->get_result();

        if ($result_account->num_rows > 0) {
            $row_account = $result_account->fetch_assoc();
            $client_id = $row_account['ID_client'];

            $sql_client = "SELECT First_name, Last_name, Patronymic FROM Clients WHERE ID_client=?";
            $stmt_client = $this->conn->prepare($sql_client);
            $stmt_client->bind_param("i", $client_id);
            $stmt_client->execute();
            $result_client = $stmt_client->get_result();

            if ($result_client->num_rows > 0) {
                $row_client = $result_client->fetch_assoc();
                $first_name = $row_client['First_name'];
                $last_name = $row_client['Last_name'];
                $patronymic = $row_client['Patronymic'];
                return $last_name . ' ' . mb_substr($first_name, 0, 1) . '.' . mb_substr($patronymic, 0, 1) . '.';
            }
        }
        return 'Неизвестный клиент';
    }

    public function getAccountCurrency($account_id) {
        $sql_account = "SELECT Currency FROM Accounts WHERE ID_accounts=?";
        $stmt_account = $this->conn->prepare($sql_account);
        $stmt_account->bind_param("i", $account_id);
        $stmt_account->execute();
        $result_account = $stmt_account->get_result();

        if ($result_account->num_rows > 0) {
            $row_account = $result_account->fetch_assoc();
            return $row_account['Currency'];
        }
        return 'Неизвестная валюта';
    }

    //новая транзакция
    public function create($recipient_account_id, $sending_account_id, $type_transaction, $amount_transaction) {
        $sql = "INSERT INTO Transactions (ID_recipient_account, ID_sending_account, Type_transaction, Amount_transaction, Date_transaction)
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisd", $recipient_account_id, $sending_account_id, $type_transaction, $amount_transaction);

        return $stmt->execute();
    }
}
?>
