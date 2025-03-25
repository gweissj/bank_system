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
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Подозрительные транзакции</title>
</head>
<body>
    <h1>Подозрительные транзакции</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Клиент (ФИО)</th>
                <th>Номер счёта</th>
                <th>Сумма транзакции</th>
                <th>Дата транзакции</th>
                <th>Тип операции</th>
                <th>Отклонение от средней суммы (в разах)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['First_name'] . ' ' . $row['Last_name']); ?></td>
                        <td><?= htmlspecialchars($row['Account_ID']); ?></td>
                        <td><?= htmlspecialchars($row['Amount']); ?></td>
                        <td><?= htmlspecialchars($row['Transaction_date']); ?></td>
                        <td><?= htmlspecialchars($row['Operation_type']); ?></td>
                        <td><?= htmlspecialchars(number_format($row['Deviation'], 2)); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Нет подозрительных транзакций.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$html_content = ob_get_clean();

header('Content-Type: application/vnd.ms-word');
header('Content-Disposition: attachment; filename="suspicious_transactions.doc"');
echo $html_content;
exit;