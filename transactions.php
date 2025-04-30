<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Transaction.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$user_id = $_SESSION['user_id'];

$transaction = new Transaction($conn);

$transactions = $transaction->getAllByClientId($user_id);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История транзакций</title>
    <link rel="stylesheet" href="styli.css">
</head>
<body>
    <div class="container">
        <h1>История транзакций</h1>
        <div class="menu">
            <a style="color: white;" href="dashboard.php" class="button">Назад в личный кабинет</a><br>
            <a style="color: white;" href="logout.php" class="button">Выход</a><br>
        </div>
        <?php if (!empty($transactions)): ?>
            <div class="items-container">
                <?php foreach ($transactions as $row): ?>
                    <div class="item">
                        <p><strong>Тип операции:</strong> <?= htmlspecialchars($row['Type_transaction']); ?></p>
                        <p><strong>Сумма:</strong> <?= number_format($row['Amount_transaction'], 2, '.', ' ') . ' ' . $transaction->getAccountCurrency($row['ID_sending_account']); ?></p>
                        <p><strong>Дата:</strong> <?= htmlspecialchars($row['Date_transaction']); ?></p>
                        <?php
                        $sending_account = $row['ID_sending_account'];
                        $recipient_account = $row['ID_recipient_account'];
                        $sender_name = $transaction->getClientName($sending_account);
                        $recipient_name = $transaction->getClientName($recipient_account);
                        if ($row['Type_transaction'] == 'перевод') {
                            if ($transaction->getClientName($sending_account) === $transaction->getClientName($user_id)) {
                                echo "<p><strong>Отправка денежных средств:</strong> клиенту " . htmlspecialchars($recipient_name) . "</p>";
                            } else {
                                echo "<p><strong>Поступление денежных средств:</strong> от клиента " . htmlspecialchars($sender_name) . "</p>";
                            }
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Нет транзакций.</p>
        <?php endif; ?>
    </div>
</body>
</html>