<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/LoanApplication.php';
require_once 'classes/Loan.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$loanApp = new LoanApplication($conn);
$loan = new Loan($conn);

//удаление заявки
if (isset($_GET['delete_application']) && is_numeric($_GET['delete_application'])) {
    $application_id = intval($_GET['delete_application']);
    $loanApp->deleteById($application_id, $user_id);
    header("Location: dashboard.php");
    exit();
}

//закрытие кредита
if (isset($_GET['close_loan']) && is_numeric($_GET['close_loan'])) {
    $loan_id = intval($_GET['close_loan']);
    if ($loan->requestClosure($loan_id, $user_id)) {
        echo "<script>alert('Запрос на закрытие кредита отправлен сотруднику.');</script>";
    } else {
        echo "<script>alert('Ошибка при отправке запроса.');</script>";
    }
    header("Location: dashboard.php");
    exit();
}

$applications = $loanApp->getAllByClientId($user_id);

$loans = $loan->getAllByClientId($user_id);

$closure_requests = $loan->getClosureRequestsByClientId($user_id);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="styli.css">
</head>
<body>
<div class="container">
    <h1>Личный кабинет</h1>
    <div class="menu">
        <a style="color: white;" href="accounts.php" class="button">Счета и переводы</a>
        <a style="color: white;" href="transactions.php" class="button">История транзакций</a>
        <a style="color: white;" href="loan_application.php" class="button">Подать заявку на кредит</a>
        <a style="color: white;" href="logout.php" class="button">Выход</a>
    </div>

    <h2>Мои кредиты</h2>
    <div class="items-container">
        <?php if (!empty($loans)): ?>
            <?php foreach ($loans as $row): ?>
                <div class="item">
                    <p><strong>Тип кредита:</strong> <?= htmlspecialchars($row['Type_loan']); ?></p>
                    <p><strong>Дата открытия:</strong> <?= htmlspecialchars($row['Date_open_loan']); ?></p>
                    <p><strong>Дата закрытия:</strong> <?= htmlspecialchars($row['Date_close_loan']); ?></p>
                    <p><strong>Процентная ставка:</strong> <?= htmlspecialchars($row['Interest_rate']); ?>%</p>
                    <p><strong>Сумма кредита:</strong> <?= number_format($row['Amount_loan'], 2, '.', ' '); ?></p>
                    <p><strong>Ежемесячный платеж:</strong> <?= number_format($row['Monthly_payment'], 2, '.', ' '); ?></p>
                    <?php if ($row['Having_loan'] == 1): ?>
                        <a href="?close_loan=<?= $row['ID_loan']; ?>" class="button">Закрыть кредит</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>У вас нет активных кредитов.</p>
        <?php endif; ?>
    </div>

    <h2>Заявки на кредит</h2>
    <div class="items-container">
        <?php if (!empty($applications)): ?>
            <?php foreach ($applications as $row): ?>
                <div class="item">
                    <p><strong>Тип кредита:</strong> <?= htmlspecialchars($row['Type_loan']); ?></p>
                    <p><strong>Дата подачи заявки:</strong> <?= htmlspecialchars($row['Date_applied']); ?></p>
                    <p><strong>Статус:</strong> 
                        <?php 
                        if ($row['Status'] == 'в стадии рассмотрения') {
                            echo 'На рассмотрении';
                        } elseif ($row['Status'] == 'одобрен') {
                            echo 'Одобрена';
                        } else {
                            echo 'Отклонена';
                        }
                        ?>
                    </p>
                    <p><strong>Сумма кредита:</strong> <?= number_format($row['Amount_loan'], 2, '.', ' '); ?></p>
                    <?php if ($row['Status'] == 'в стадии рассмотрения'): ?>
                        <a href="edit_application.php?id=<?= $row['ID_application']; ?>" class="button">Изменить</a>
                        <a href="?delete_application=<?= $row['ID_application']; ?>" class="button">Удалить</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>У вас нет заявок на кредит.</p>
        <?php endif; ?>
    </div>

    <h2>Запросы на закрытие кредита</h2>
    <div class="items-container">
        <?php if (!empty($closure_requests)): ?>
            <?php foreach ($closure_requests as $row): ?>
                <div class="item">
                    <p><strong>Тип кредита:</strong> <?= htmlspecialchars($row['Type_loan']); ?></p>
                    <p><strong>Статус:</strong> 
                        <?php 
                        if ($row['Status'] == 'в стадии рассмотрения') {
                            echo 'На рассмотрении';
                        } elseif ($row['Status'] == 'одобрен') {
                            echo 'Одобрен';
                        } else {
                            echo 'Отклонен';
                        }
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>У вас нет запросов на закрытие кредита.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>