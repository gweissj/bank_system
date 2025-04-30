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
$export_type = $_GET['export'] ?? null;

switch ($filter_period) {
    case 'month':
        $interval = "1 MONTH";
        break;
    case 'quarter':
        $interval = "3 MONTH";
        break;
    case 'year':
        $interval = "1 YEAR";
        break;
    default:
        $interval = "1 MONTH";
}

$sql = "
    SELECT 
        s.First_name, 
        s.Last_name, 
        s.Post, 
        COUNT(l.ID_loan) AS Loan_Count, 
        SUM(l.Amount_loan) AS Total_Amount
    FROM Loans l
    JOIN Staff s ON l.ID_staff = s.ID_staff
    WHERE l.Date_open_loan >= DATE_SUB(CURDATE(), INTERVAL $interval)
    GROUP BY s.ID_staff
    ORDER BY Loan_Count DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

//экспорт в Word
function exportToWord($data) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Отчет по сотрудникам</title>
    </head>
    <body>
        <h1>Отчет по сотрудникам</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>ФИО сотрудника</th>
                    <th>Должность</th>
                    <th>Число оформленных кредитов</th>
                    <th>Общая сумма выданных кредитов</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data)): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['First_name'] . ' ' . $row['Last_name']); ?></td>
                            <td><?= htmlspecialchars($row['Post']); ?></td>
                            <td><?= htmlspecialchars($row['Loan_Count']); ?></td>
                            <td><?= htmlspecialchars(number_format($row['Total_Amount'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Нет данных за указанный период.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    $html_content = ob_get_clean();

    header('Content-Type: application/vnd.ms-word');
    header('Content-Disposition: attachment; filename="admin_loan_report.doc"');
    echo $html_content;
    exit;
}

//экспорт в CSV
function exportToCsv($data) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="admin_loan_report.csv"');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        'ФИО сотрудника',
        'Должность',
        'Число оформленных кредитов',
        'Общая сумма выданных кредитов'
    ]);

    foreach ($data as $row) {
        fputcsv($output, [
            $row['First_name'] . ' ' . $row['Last_name'],
            $row['Post'],
            $row['Loan_Count'],
            number_format($row['Total_Amount'], 2)
        ]);
    }

    fclose($output);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if ($export_type === 'word') {
    exportToWord($data);
} elseif ($export_type === 'csv') {
    exportToCsv($data);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчет по сотрудникам</title>
    <link rel="stylesheet" href="styli.css">
</head>
<body>
<div class="container">
    <h1>Отчет по сотрудникам</h1>

    <a href="staff_dashboard.php" class="button">Назад в панель сотрудника</a><br>

    <form method="GET" action="admin_loan.php">
        <label for="period">Период:</label>
        <select name="period" id="period">
            <option value="month" <?= $filter_period === 'month' ? 'selected' : '' ?>>Месяц</option>
            <option value="quarter" <?= $filter_period === 'quarter' ? 'selected' : '' ?>>Квартал</option>
            <option value="year" <?= $filter_period === 'year' ? 'selected' : '' ?>>Год</option>
        </select>

        <button type="submit">Применить фильтр</button>
    </form>

    <h2>Результаты</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ФИО сотрудника</th>
                <th>Должность</th>
                <th>Число оформленных кредитов</th>
                <th>Общая сумма выданных кредитов</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['First_name'] . ' ' . $row['Last_name']); ?></td>
                        <td><?= htmlspecialchars($row['Post']); ?></td>
                        <td><?= htmlspecialchars($row['Loan_Count']); ?></td>
                        <td><?= htmlspecialchars(number_format($row['Total_Amount'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Нет данных за указанный период.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div>
        <a href="admin_loan.php?period=<?= $filter_period ?>&export=word" class="button">Экспорт в Word</a>
        <a href="admin_loan.php?period=<?= $filter_period ?>&export=csv" class="button">Экспорт в CSV</a>
    </div>
</div>
</body>
</html>