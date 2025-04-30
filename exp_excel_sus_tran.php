<?php
session_start();
require_once 'classes/Database.php';

if (!isset($_SESSION['staff_id']) || !is_numeric($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$filter_period = $_GET['period'] ?? 'month';
$filter_operation = $_GET['operation'] ?? '';

$sql = "
    SELECT 
        c.First_name, 
        c.Last_name, 
        a.ID_accounts AS Account_ID, 
        t.Amount_transaction AS Amount, 
        t.Date_transaction AS Transaction_date, 
        t.Type_transaction AS Operation_type,
        t.Amount_transaction / avg_data.Avg_Amount AS Deviation
    FROM Transactions t
    JOIN Accounts a ON t.ID_sending_account = a.ID_accounts
    JOIN Clients c ON a.ID_client = c.ID_client
    LEFT JOIN (
        SELECT 
            ID_sending_account, 
            AVG(Amount_transaction) AS Avg_Amount
        FROM Transactions
        WHERE Date_transaction >= DATE_SUB(CURDATE(), INTERVAL 1 $filter_period)
        GROUP BY ID_sending_account
    ) avg_data ON t.ID_sending_account = avg_data.ID_sending_account
    WHERE t.Date_transaction >= DATE_SUB(CURDATE(), INTERVAL 1 $filter_period)
      AND t.Amount_transaction > 5 * avg_data.Avg_Amount
      AND (t.Type_transaction = ? OR ? = '')
";

$stmt = $conn->prepare($sql);
$filter_operation_param = $filter_operation ?: '';
$stmt->bind_param("ss", $filter_operation_param, $filter_operation_param);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="suspicious_transactions.csv"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    'Клиент (ФИО)',
    'Номер счёта',
    'Сумма транзакции',
    'Дата транзакции',
    'Тип операции',
    'Отклонение от средней суммы (в разах)'
]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['First_name'] . ' ' . $row['Last_name'],
        $row['Account_ID'],
        $row['Amount'],
        $row['Transaction_date'],
        $row['Operation_type'],
        number_format($row['Deviation'], 2)
    ]);
}

fclose($output);
exit;