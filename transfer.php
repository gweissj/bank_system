<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Account.php';
require_once 'classes/Transaction.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$account = new Account($conn);
$transaction = new Transaction($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['sender_account']) || empty($_POST['recipient_phone']) || empty($_POST['amount'])) {
        echo "<script>alert('Заполните все поля.'); window.location.href='accounts.php';</script>";
        exit;
    }

    $sender_account_id = intval($_POST['sender_account']);
    $recipient_phone = trim($_POST['recipient_phone']);
    $amount = floatval($_POST['amount']);

    $sender_balance = $account->getBalanceById($sender_account_id);
    if ($sender_balance === null) {
        echo "<script>alert('Выбранный счет не найден.'); window.location.href='accounts.php';</script>";
        exit;
    }

    if ($sender_balance < $amount) {
        echo "<script>alert('Недостаточно средств на выбранном счете.'); window.location.href='accounts.php';</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT ID_client FROM Clients WHERE Phone_number=?");
    $stmt->bind_param("s", $recipient_phone);
    $stmt->execute();
    $recipient_result = $stmt->get_result();

    if ($recipient_result->num_rows === 0) {
        echo "<script>alert('Получатель не найден.'); window.location.href='accounts.php';</script>";
        exit;
    }

    $recipient_row = $recipient_result->fetch_assoc();
    $recipient_client_id = $recipient_row['ID_client'];

    $recipient_account_id = $account->getActiveAccountIdByClientId($recipient_client_id);
    if ($recipient_account_id === null) {
        echo "<script>alert('Активный счет получателя не найден.'); window.location.href='accounts.php';</script>";
        exit;
    }

    $conn->begin_transaction();
    try {
        //уменьшение баланса
        $account->updateBalance($sender_account_id, $sender_balance - $amount);

        //увеличение баланса
        $recipient_balance = $account->getBalanceById($recipient_account_id);
        $account->updateBalance($recipient_account_id, $recipient_balance + $amount);

        //добавление транзакции
        $transaction->create($recipient_account_id, $sender_account_id, 'перевод', $amount);

        $conn->commit();
        echo "<script>alert('Перевод выполнен успешно.'); window.location.href='transactions.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Ошибка при выполнении перевода.'); window.location.href='accounts.php';</script>";
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Перевод средств</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Перевод средств</h1>
    <a href="dashboard.php" class="button">Назад в личный кабинет</a><br>
    <a href="logout.php" class="button">Выход</a><br>
    <form action="transfer.php" method="POST" class="transfer-form">
        <label for="sender_account">Счет отправителя:</label>
        <select name="sender_account" id="sender_account" required>
            <?php
            $accounts = $account->getAllByClientId($user_id);
            foreach ($accounts as $acc) {
                echo "<option value='{$acc['ID_accounts']}'>{$acc['Account_type']} (Баланс: {$acc['Balance']})</option>";
            }
            ?>
        </select><br>
        <label for="recipient_phone">Номер телефона получателя:</label>
        <input type="text" id="recipient_phone" name="recipient_phone" required placeholder="+7XXXXXXXXXX или 8XXXXXXXXXX"><br>
        <label for="amount">Сумма перевода:</label>
        <input type="number" id="amount" name="amount" required><br>
        <button type="submit" class="button">Перевести</button>
    </form>
</div>
</body>
</html>