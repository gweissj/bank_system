<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Client.php';

$database = new Database();
$conn = $database->getConnection();

$Client = new Client($conn);

$query = "SELECT First_name, Last_name, Email, Password, Plain_password FROM Clients";
$stmt = $conn->prepare($query);
$stmt->execute();

$result = $stmt->get_result();
$Clients = $result->fetch_all(MYSQLI_ASSOC);

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user_id = $Client->login($email, $password);
    if ($user_id) {
        $_SESSION['user_id'] = $user_id;
        header("Location: dashboard.php");
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
    <title>Вход для клиентов</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Вход для клиентов</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST" class="content-box">
            <label for="Client-select">Выберите клиента:</label>
            <select id="Client-select" name="Client-select">
                <option value="">-- Выберите клиента --</option>
                <?php foreach ($Clients as $Client): ?>
                    <option value="<?php echo htmlspecialchars($Client['Email']); ?>" 
                            data-password="<?php echo htmlspecialchars($Client['Plain_password']); ?>">
                        <?php echo htmlspecialchars($Client['First_name'] . ' ' . $Client['Last_name']); ?>
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
        document.getElementById('Client-select').addEventListener('change', function () {
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

        //видимость пароля
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