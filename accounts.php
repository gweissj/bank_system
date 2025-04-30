<?php
session_start();
require_once 'classes/Account.php';
require_once 'classes/Database.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$account = new Account($conn);

$accounts = $account->getAllByClientId($user_id);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_account'])) {
    $account_type = $_POST['account_type'];
    $currency = $_POST['currency'];
    $initial_balance = $_POST['initial_balance'] ?? 0;

    if (empty($account_type) || empty($currency) || !is_numeric($initial_balance) || $initial_balance < 0) {
        echo "<script>alert('Ошибка: Пожалуйста, заполните все поля корректно.'); window.location.href='accounts.php';</script>";
        exit;
    }

    if ($account->create($user_id, $account_type, $currency, $initial_balance)) {
        echo "<script>alert('Счет успешно открыт!'); window.location.href='accounts.php';</script>";
    } else {
        echo "<script>alert('Ошибка при открытии счета.'); window.location.href='accounts.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои счета</title>
    <link rel="stylesheet" href="styli.css">
</head>
<body>
    <div class="container">
        <h1>Мои счета</h1>
        <a href="dashboard.php" class="button">Назад в личный кабинет</a><br>
        <a href="logout.php" class="button">Выход</a><br>

        <?php if (!empty($accounts)): ?>
            <table class="accounts-table">
                <thead>
                    <tr>
                        <th>Тип счета</th>
                        <th>Баланс</th>
                        <th>Дата открытия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Account_type']); ?></td>
                            <td><?php 
                                $formattedBalance = number_format($row['Balance'], 2, '.', ' ');
                                echo htmlspecialchars($formattedBalance . ' ' . $row['Currency']);
                            ?></td>
                            <td><?php echo htmlspecialchars($row['Opened_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>У вас нет активных счетов.</p>
        <?php endif; ?>

        <h2>Перевод средств</h2>
        <form action="transfer.php" method="POST" class="transfer-form">
            <label for="sender_account">Выберите счет отправителя:</label>
            <select name="sender_account" id="sender_account" required>
                <?php if (!empty($accounts)): ?>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?php echo htmlspecialchars($acc['ID_accounts']); ?>">
                            <?php 
                                echo htmlspecialchars($acc['Account_type']) . ' - ' . 
                                     number_format($acc['Balance'], 2, '.', ' ') . ' ' . 
                                     htmlspecialchars($acc['Currency']);
                            ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Нет доступных счетов</option>
                <?php endif; ?>
            </select><br>

            <label for="recipient_phone">Номер телефона получателя:</label>
            <input type="text" id="recipient_phone" name="recipient_phone" required placeholder="+7XXXXXXXXXX или 8XXXXXXXXXX"><br>
            
            <label for="amount">Сумма перевода:</label>
            <input type="number" id="amount" name="amount" required><br>
            
            <button type="submit" class="button">Перевести</button>
        </form>

        <h2>Открыть новый счет</h2>
        <form action="accounts.php" method="POST" class="open-account-form" onsubmit="return validateAccountForm()">
            <label for="account_type">Тип счета:</label>
            <select name="account_type" id="account_type" required>
                <option value="">-- Выберите тип счета --</option>
                <option value="депозитный">Депозитный</option>
                <option value="кредитный">Кредитный</option>
                <option value="дебетовый">Дебетовый</option>
                <option value="инвестиционный">Инвестиционный</option>
            </select><br>
            <label for="currency">Валюта:</label>
            <select name="currency" id="currency" required>
                <option value="">-- Выберите валюту --</option>
                <option value="рубль">рубль</option>
                <option value="доллар">доллар</option>
                <option value="евро">евро</option>
                <option value="юани">юани</option>
            </select><br>
            <label for="initial_balance">Начальный баланс:</label>
            <input type="number" name="initial_balance" id="initial_balance" min="0"><br> 
            <button type="submit" name="create_account" class="button">Открыть счет</button>
        </form>
        <script>
            function validateAccountForm() {
                const accountType = document.getElementById('account_type').value;
                const currency = document.getElementById('currency').value;
                const initialBalance = parseFloat(document.getElementById('initial_balance').value);

                if (!accountType || !currency || isNaN(initialBalance) || initialBalance < 0) {
                    alert('Пожалуйста, заполните все поля корректно.');
                    return false; 
                }
                return true; 
            }
        </script>

<h2>Просмотр истории транзакций</h2>
<form action="transaction_report_client.php" method="GET" class="report-form">
    <label for="account_id">Выберите счет:</label>
    <select name="account_id" id="account_id" required>
        <?php if (!empty($accounts)): ?>
            <?php foreach ($accounts as $acc): ?>
                <option value="<?php echo htmlspecialchars($acc['ID_accounts']); ?>">
                    <?php 
                        echo htmlspecialchars($acc['Account_type']) . ' - ' . 
                             number_format($acc['Balance'], 2, '.', ' ') . ' ' . 
                             htmlspecialchars($acc['Currency']);
                    ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="" disabled>Нет доступных счетов</option>
        <?php endif; ?>
    </select><br>

    <label for="start_date">Дата начала:</label>
    <input type="date" id="start_date" name="start_date" required><br>

    <label for="end_date">Дата окончания:</label>
    <input type="date" id="end_date" name="end_date" required><br>

    <button type="submit" class="button">Просмотреть отчет</button>
</form>
    </div>
</body>
</html>