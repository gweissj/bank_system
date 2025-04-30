<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Staff.php';

$database = new Database();
$conn = $database->getConnection();
$staff = new Staff($conn);

$query = "SELECT ID_staff, First_name, Last_name, Email, Password, Plain_password FROM Staff";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$staff_members = $result->fetch_all(MYSQLI_ASSOC);

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user_id = $staff->login($email, $password);
    if ($user_id) {
        $_SESSION['staff_id'] = $user_id;
        header("Location: staff_dashboard.php");
        exit;
    } else {
        $message = "Неверный email или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход для сотрудников</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Вход для сотрудников</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="staff_login.php" method="POST" class="content-box">
            <label for="staff-select">Выберите сотрудника:</label>
            <select id="staff-select" name="staff-select">
                <option value="">-- Выберите сотрудника --</option>
                <?php foreach ($staff_members as $member): ?>
                    <option value="<?php echo htmlspecialchars($member['Email']); ?>" 
                            data-password="<?php echo htmlspecialchars($member['Plain_password']); ?>">
                        <?php echo htmlspecialchars($member['First_name'] . ' ' . $member['Last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Пароль:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">Показать</button>
            </div>

            <button type="submit" class="button">Войти</button>
        </form>
        <a href="index.php" class="back-button">Вернуться на главную</a>
    </div>

    <script>
        //автозаполнение 
        document.getElementById('staff-select').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            if (selectedOption.value) {
                emailInput.value = selectedOption.value;
                passwordInput.value = selectedOption.getAttribute('data-password');
            } else {
                emailInput.value = '';
                passwordInput.value = '';
            }
        });

        //переключение видимости пароля
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Скрыть';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Показать';
            }
        }
    </script>
</body>
</html>