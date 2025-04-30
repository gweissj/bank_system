<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Client.php';

$database = new Database();
$conn = $database->getConnection();
$conn->set_charset("utf8");

$client = new Client($conn);

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['patronymic']) &&
        isset($_POST['date_of_birth']) && isset($_POST['phone_number']) && isset($_POST['passport_number']) &&
        isset($_POST['email']) && isset($_POST['gender']) && isset($_POST['password'])
    ) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $patronymic = trim($_POST['patronymic']);
        $date_of_birth = $_POST['date_of_birth'];
        $phone_number = trim($_POST['phone_number']);
        $passport_number = trim($_POST['passport_number']);
        $email = trim($_POST['email']);
        $gender = $_POST['gender'];
        $password = $_POST['password'];

        $isValid = true;

        if (strtotime($date_of_birth) >= strtotime('today')) {
            $message = "Дата рождения не может быть позже текущей даты.";
            $isValid = false;
        }
        elseif (!preg_match('/^(?:\+7|8)\d{10}$/', $phone_number)) {
            $message = "Номер телефона должен начинаться с +7 или 8 и содержать 10 цифр после кода.";
            $isValid = false;
        }
        elseif (!preg_match('/^\d{10}$/', $passport_number)) {
            $message = "Номер паспорта должен содержать ровно 10 цифр.";
            $isValid = false;
        }
        elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@(mail\.ru|yandex\.ru|bk\.ru|gmail\.com)$/', $email)) {
            $message = "Email должен быть в доменах @mail.ru, @yandex.ru, @bk.ru или @gmail.com.";
            $isValid = false;
        }
        elseif (strlen($password) < 6) {
            $message = "Пароль должен содержать не менее 6 символов.";
            $isValid = false;
        }

        if ($isValid) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $gender = $_POST['gender'] === "1" ? chr(1) : chr(0);//добавил сюда это, чтобы регистрация работала
        
            $stmt = $conn->prepare("INSERT INTO Clients (First_name, Last_name, Patronymic, Date_of_birth, Phone_number, Passport_number, Email, Gender, Password, Plain_password)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssissss", 
                $first_name, 
                $last_name, 
                $patronymic, 
                $date_of_birth, 
                $phone_number, 
                $passport_number, 
                $email, 
                $gender, 
                $hashed_password, 
                $password
            );
        
            if ($stmt->execute()) {
                echo "<script>alert('Регистрация успешна!'); window.location.href='index.php';</script>";
            } else {
                $message = "Ошибка регистрации: " . $stmt->error;
            }
        
            $stmt->close();
        }
    } else {
        $message = "Необходимо заполнить все поля формы.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <style>
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .message {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="text"],
        input[type="date"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-button {
            display: block;
            margin-top: 10px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Регистрация</h1>
        <?php if (!empty($message)): ?>
            <p class="error-message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST" class="content-box">
            <label for="first_name">Имя:</label>
            <input type="text" name="first_name" required><br>
            <label for="last_name">Фамилия:</label>
            <input type="text" name="last_name" required><br>
            <label for="patronymic">Отчество:</label>
            <input type="text" name="patronymic"><br>
            <label for="date_of_birth">Дата рождения:</label>
            <input type="date" name="date_of_birth" required><br>
            <label for="phone_number">Телефон:</label>
            <input type="text" name="phone_number" required><br>
            <label for="passport_number">Номер паспорта:</label>
            <input type="text" name="passport_number" required><br>
            <label for="email">Email:</label>
            <input type="email" name="email" required><br>
            <label for="gender">Пол (1 - мужской, 0 - женский):</label>
            <input type="number" name="gender" min="0" max="1" required><br>
            <label for="password">Пароль:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">Показать</button>
            </div>
            <button type="submit" class="button">Зарегистрироваться</button>
        </form>
        <a href="index.php" class="back-button">Вернуться на главную</a>
    </div>

    <script>
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