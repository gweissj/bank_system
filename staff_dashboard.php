<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Staff.php';
require_once 'classes/Loan.php';

if (!isset($_SESSION['staff_id']) || !is_numeric($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$staff = new Staff($conn);
$staff_id = intval($_SESSION['staff_id']);

if (!$staff->getStaffInfo($staff_id)) {
    header("Location: staff_login.php");
    exit;
}

$is_credit_department_staff = $staff->isCreditDepartmentStaff($staff_id);
$is_admin = $staff->isAdmin($staff_id);

//одобрение или отклонение кредита
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['closure_request_id']) && isset($_POST['action'])) {
        $closure_request_id = intval($_POST['closure_request_id']);
        $action = $_POST['action'];

        if ($action == 'approve') {
            $sql = "SELECT ID_loan FROM Loan_Closure_Requests WHERE ID_closure_request = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $closure_request_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $loan_id = $row['ID_loan'];

                $loan = new Loan($conn);
                $loan->closeLoan($loan_id);

                $sql_update = "UPDATE Loan_Closure_Requests SET Status = 'одобрен' WHERE ID_closure_request = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("i", $closure_request_id);
                $stmt_update->execute();
            }
        } elseif ($action == 'reject') {
            $sql_update = "UPDATE Loan_Closure_Requests SET Status = 'отклонен' WHERE ID_closure_request = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $closure_request_id);
            $stmt_update->execute();
        }
    }
}

//закрытие кредита
$sql_requests = "SELECT lcr.ID_closure_request, l.Type_loan, c.First_name, c.Last_name, lcr.Status 
                 FROM Loan_Closure_Requests lcr
                 JOIN Loans l ON lcr.ID_loan = l.ID_loan
                 JOIN Clients c ON lcr.ID_client = c.ID_client
                 WHERE lcr.Status = 'в стадии рассмотрения'";
$result_requests = $conn->query($sql_requests);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель сотрудника</title>
    <link rel="stylesheet" href="styli.css">
</head>
<body>
<div class="container">
    <h1>Панель сотрудника</h1>

    <a href="staff_loan_applications.php" class="button">Заявки на кредит</a><br>
    <a href="suspicious_transactions.php" class="button">Просмотреть подозрительные транзакции</a><br>
    <a href="loan_profit_report.php" class="button">Отчёт о прибыли по кредитам</a><br>

    <?php if ($is_admin): ?>
        <a href="admin_loan.php" class="button">Отчет по сотрудникам</a><br>
    <?php endif; ?>

    <a href="staff_logout.php" class="button">Выход</a><br>

    <?php
    if ($is_credit_department_staff) {
        echo "<p>Вы работаете в кредитном отделе.</p>";
    } else {
        echo "<p>Вы работаете в другой роли.</p>";
    }
    ?>

    <h2>Заявки на закрытие кредита</h2>
    <div class="items-container">
        <?php if ($result_requests->num_rows > 0): ?>
            <?php while ($row = $result_requests->fetch_assoc()): ?>
                <div class="item">
                    <p><strong>Тип кредита:</strong> <?= htmlspecialchars($row['Type_loan']); ?></p>
                    <p><strong>Клиент:</strong> <?= htmlspecialchars($row['First_name'] . ' ' . $row['Last_name']); ?></p>
                    <p><strong>Статус:</strong> <?= htmlspecialchars($row['Status']); ?></p>
                    <form method="POST">
                        <input type="hidden" name="closure_request_id" value="<?= $row['ID_closure_request']; ?>">
                        <button type="submit" name="action" value="approve">Одобрить</button>
                        <button type="submit" name="action" value="reject">Отклонить</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Нет новых запросов на закрытие кредита.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>