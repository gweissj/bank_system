<?php
session_start();
require_once 'classes/Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$account_id = $_GET['account_id'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

if (!$account_id || !$start_date || !$end_date) {
    die("Ошибка: Необходимо указать счет и период.");
}

$query_initial_balance = "
    SELECT Balance 
    FROM Accounts 
    WHERE ID_accounts = ?
";
$stmt_initial_balance = $conn->prepare($query_initial_balance);
$stmt_initial_balance->bind_param("i", $account_id);
$stmt_initial_balance->execute();
$result_initial_balance = $stmt_initial_balance->get_result();
$initial_balance = 0;

if ($result_initial_balance->num_rows > 0) {
    $row_initial_balance = $result_initial_balance->fetch_assoc();
    $initial_balance = $row_initial_balance['Balance'];
}

$query_transactions = "
    SELECT 
        Date_transaction AS transaction_date,
        Type_transaction AS operation_type,
        Amount_transaction AS amount,
        ID_sending_account,
        ID_recipient_account
    FROM Transactions
    WHERE (ID_sending_account = ? OR ID_recipient_account = ?)
      AND Date_transaction BETWEEN ? AND ?
    ORDER BY Date_transaction ASC
";

$stmt_transactions = $conn->prepare($query_transactions);
$stmt_transactions->bind_param("iiss", $account_id, $account_id, $start_date, $end_date);
$stmt_transactions->execute();
$result_transactions = $stmt_transactions->get_result();
$transactions = $result_transactions->fetch_all(MYSQLI_ASSOC);

$current_balance = $initial_balance;
foreach ($transactions as &$transaction) {
    $transaction['balance_before'] = $current_balance;

    if ($transaction['ID_sending_account'] == $account_id) {
        //текущий счет — отправитель, списываем сумму
        $current_balance -= $transaction['amount'];
    } elseif ($transaction['ID_recipient_account'] == $account_id) {
        //текущий счет — получатель, прибавляем сумму
        $current_balance += $transaction['amount'];
    }

    $transaction['balance_after'] = $current_balance;
}
unset($transaction);

//экспорт в Excel
function exportToExcel($data, $filename) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        'Дата транзакции',
        'Тип операции',
        'Баланс до',
        'Сумма',
        'Баланс после'
    ]);

    foreach ($data as $row) {
        fputcsv($output, [
            $row['transaction_date'],
            $row['operation_type'],
            number_format($row['balance_before'], 2),
            number_format($row['amount'], 2),
            number_format($row['balance_after'], 2)
        ]);
    }

    fclose($output);
    exit;
}

//экспорт в Word
function exportToWord($data, $filename) {
    header("Content-type: application/vnd.ms-word");
    header("Content-Disposition: attachment;Filename=" . $filename . ".doc");
    echo "<html>";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
    echo "<body>";
    echo "<table border='1'>";
    echo "<tr><th>Дата транзакции</th><th>Тип операции</th><th>Баланс до</th><th>Сумма</th><th>Баланс после</th></tr>";
    foreach ($data as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['transaction_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['operation_type']) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['balance_before'], 2)) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['amount'], 2)) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['balance_after'], 2)) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</body>";
    echo "</html>";
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($transactions, "Transaction_Report_{$account_id}");
} elseif (isset($_GET['export']) && $_GET['export'] === 'word') {
    exportToWord($transactions, "Transaction_Report_{$account_id}");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчет по транзакциям</title>
    <link rel="stylesheet" href="styli.css">
</head>
<body>
    <div class="container">
        <h1>История транзакций</h1>
        <p>Счет: <?php echo htmlspecialchars($account_id); ?></p>
        <p>Период: <?php echo htmlspecialchars($start_date); ?> - <?php echo htmlspecialchars($end_date); ?></p>

        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Дата транзакции</th>
                    <th>Тип операции</th>
                    <th>Баланс до</th>
                    <th>Сумма</th>
                    <th>Баланс после</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['operation_type']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['balance_before'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['balance_after'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Нет транзакций за указанный период.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="export-buttons">
            <a href="?account_id=<?php echo $account_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=excel" class="button">Скачать в Excel</a>
            <a href="?account_id=<?php echo $account_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=word" class="button">Скачать в Word</a>
        </div>
    </div>
</body>
</html>