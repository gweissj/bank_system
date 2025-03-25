<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/LoanApplication.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$loanApp = new LoanApplication($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_type = $_POST['loan_type'];
    $amount = $_POST['amount'];

    if (!is_numeric($amount) || $amount <= 0) {
        echo "<script>alert('Сумма кредита должна быть положительным числом.'); window.location.href='loan_application.php';</script>";
        exit;
    }

    if ($loanApp->create($user_id, $loan_type, $amount)) {
        echo "<script>alert('Заявка отправлена!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Ошибка при отправке заявки.'); window.location.href='loan_application.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подать заявку на кредит</title>
    <link rel="stylesheet" href="styli.css">
</head>
<body>
    <div class="container">
        <h1>Подать заявку на кредит</h1>
        <a href="dashboard.php" class="button">Назад в личный кабинет</a><br>
        <a href="logout.php" class="button">Выход</a><br>
        <form action="loan_application.php" method="POST" class="loan-application-form" onsubmit="return validateForm()">
            <label for="loan_type">Тип кредита:</label>
            <select name="loan_type" required>
                <option value="автомобильный">Автомобильный</option>
                <option value="ипотечный">Ипотечный</option>
                <option value="потребительский">Потребительский</option>
                <option value="образовательный">Образовательный</option>
            </select><br>
            <label for="amount">Сумма кредита:</label>
            <input type="number" name="amount" required><br>
            <button type="submit" class="button">Отправить заявку</button>
        </form>
        <script>
            function validateForm() {
                const amountInput = document.querySelector('input[name="amount"]');
                const amountValue = parseFloat(amountInput.value);

                if (isNaN(amountValue) || amountValue <= 0) {
                    alert('Сумма кредита должна быть положительным числом.');
                    return false; 
                }
                return true;
            }
        </script>
    </div>
</body>
</html>