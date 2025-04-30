<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/LoanApplication.php';

$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$application_id = intval($_GET['id']);

$loanApp = new LoanApplication($conn);

$current_data = $loanApp->getById($application_id, $user_id);

if (!$current_data) {
    header("Location: dashboard.php");
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_type = $_POST['type_loan'];
    $new_amount = floatval($_POST['amount_loan']);

    if ($loanApp->update($application_id, $user_id, $new_type, $new_amount)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Ошибка обновления заявки.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование заявки</title>
    <link rel="stylesheet" href="styli.css">
    <style>
        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .form-buttons .button {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-control {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Редактирование заявки</h1>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <div class="items-container">
        <div class="item">
            <form method="POST" class="loan-application-form">
                <label for="type_loan">Тип кредита:</label>
                <select name="type_loan" id="type_loan" class="form-control" required>
                    <option value="Потребительский" <?php echo ($current_data['Type_loan'] == 'Потребительский') ? 'selected' : ''; ?>>Потребительский</option>
                    <option value="Ипотечный" <?php echo ($current_data['Type_loan'] == 'Ипотечный') ? 'selected' : ''; ?>>Ипотечный</option>
                    <option value="Автомобильный" <?php echo ($current_data['Type_loan'] == 'Автомобильный') ? 'selected' : ''; ?>>Автомобильный</option>
                    <option value="Образовательный" <?php echo ($current_data['Type_loan'] == 'Образовательный') ? 'selected' : ''; ?>>Образовательный</option>
                </select>

                <label for="amount_loan">Сумма кредита:</label>
                <input type="number" name="amount_loan" id="amount_loan" value="<?php echo htmlspecialchars($current_data['Amount_loan']); ?>" required class="form-control">

                <div class="form-buttons">
                    <button type="submit" class="button">Сохранить</button>
                    <a href="dashboard.php" class="button back-button">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>