<?php
session_start();
require_once 'classes/Database.php';

if (!isset($_SESSION['staff_id']) || !is_numeric($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

function getLoanProfitData($conn) {
    $sql = "
        SELECT 
            l.Type_loan AS Loan_Type,
            SUM(
                (CASE 
                    WHEN l.Date_close_loan IS NOT NULL THEN TIMESTAMPDIFF(MONTH, l.Date_open_loan, l.Date_close_loan)
                    ELSE TIMESTAMPDIFF(MONTH, l.Date_open_loan, CURDATE())
                END) * l.Monthly_payment - l.Amount_loan
            ) AS Profit
        FROM Loans l
        WHERE l.Type_loan IN ('автомобильный', 'ипотечный', 'потребительский', 'образовательный')
          AND l.Having_loan = b'1'
        GROUP BY l.Type_loan
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->execute();
    return $stmt->get_result();
}

if (isset($_GET['action']) && $_GET['action'] === 'export_word') {
    $result = getLoanProfitData($conn);

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Отчёт о прибыли по типам кредитов</title>
    </head>
    <body>
        <h1>Отчёт о прибыли по типам кредитов</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Тип кредита</th>
                    <th>Общая прибыль</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Loan_Type']); ?></td>
                            <td><?= htmlspecialchars(number_format($row['Profit'], 2)); ?> ₽</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">Нет данных о прибыли.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    $html_content = ob_get_clean();

    header('Content-Type: application/vnd.ms-word');
    header('Content-Disposition: attachment; filename="loan_profit_report.doc"');
    echo $html_content;
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    $result = getLoanProfitData($conn);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="loan_profit_report.csv"');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, ['Тип кредита', 'Общая прибыль']);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['Loan_Type'],
            number_format($row['Profit'], 2)
        ]);
    }

    fclose($output);
    exit;
}

$result = getLoanProfitData($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Отчёт о прибыли по типам кредитов</title>
</head>
<body>
    <h1>Отчёт о прибыли по типам кредитов</h1>
    <a href="dashboard.php" class="button">Назад в личный кабинет</a><br>
    <div style="margin-bottom: 20px;">
        <a href="?action=export_word" style="text-decoration: none;">
            <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer;">
                Экспорт в Word
            </button>
        </a>
        <a href="?action=export_excel" style="text-decoration: none;">
            <button style="background-color: #008CBA; color: white; padding: 10px 20px; border: none; cursor: pointer;">
                Экспорт в Excel
            </button>
        </a>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>Тип кредита</th>
                <th>Общая прибыль</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Loan_Type']); ?></td>
                        <td><?= htmlspecialchars(number_format($row['Profit'], 2)); ?> ₽</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Нет данных о прибыли.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>