

![image](https://github.com/user-attachments/assets/4d864279-43ab-4931-955e-ca11b005ed8e)

![image](https://github.com/user-attachments/assets/0d8ad528-dec0-420b-a5a0-c540fa7ca2f5)

![image](https://github.com/user-attachments/assets/29109de0-f239-4e9f-ba72-eae98147630f)

![image](https://github.com/user-attachments/assets/8e1ed89e-895f-4d0e-b349-5dc6509805cb)

![image](https://github.com/user-attachments/assets/60afa8e7-0ab5-4132-9527-38b14142ae3e)

![image](https://github.com/user-attachments/assets/ea56f054-33e6-4e89-b9c6-b088bd7ab768)

![image](https://github.com/user-attachments/assets/64575232-0433-45e0-9442-3d7bd43f2479)

![image](https://github.com/user-attachments/assets/0da3a26e-2585-4b6f-a6e5-5b10394b333c)

![image](https://github.com/user-attachments/assets/ddb04628-1a31-420c-b5f1-2245a91695d2)




Классы PHP
<?php
// Класс счетов
class Account {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Получение всех активных счетов пользователя
    public function getAllByClientId($client_id) {
        $sql = "SELECT ID_accounts, Account_type, Balance, Currency, Opened_date 
                FROM Accounts 
                WHERE ID_client=? AND Active_account=1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }

        return $accounts;
    }

    // Создание нового счета
    public function create($client_id, $account_type, $currency, $initial_balance) {
        $sql = "INSERT INTO Accounts (ID_client, Account_type, Balance, Opened_date, Active_account, Currency) 
                VALUES (?, ?, ?, NOW(), 1, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isds", $client_id, $account_type, $initial_balance, $currency);

        return $stmt->execute();
    }

    // Получение баланса счета по его ID
    public function getBalanceById($account_id) {
        $sql = "SELECT Balance FROM Accounts WHERE ID_accounts=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['Balance'];
        }

        return null;
    }

    // Получение ID активного счета клиента
    public function getActiveAccountIdByClientId($client_id) {
        $sql = "SELECT ID_accounts FROM Accounts WHERE ID_client=? AND Active_account=1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['ID_accounts'];
        }

        return null; 
    }

    // Обновление баланса счета
    public function updateBalance($account_id, $new_balance) {
        $sql = "UPDATE Accounts SET Balance=? WHERE ID_accounts=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $new_balance, $account_id);

        return $stmt->execute();
    }
}
?>

<?php
// Класс клиентов
class Client {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Регистрация клиента
    public function register($data) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO Clients (First_name, Last_name, Patronymic, Date_of_birth, Phone_number, Passport_number, Email, Gender, Password)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisss", 
            $data['first_name'], 
            $data['last_name'], 
            $data['patronymic'], 
            $data['date_of_birth'], 
            $data['phone_number'], 
            $data['passport_number'], 
            $data['email'], 
            $data['gender'], 
            $hashed_password
        );
        return $stmt->execute();
    }

    // Авторизация клиента
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM Clients WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                return $user['ID_client'];
            }
        }
        return false;
    }
}
?>

<?php
// Класс кредитов
class Loan {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Создание нового кредита
    public function createLoan($data) {
        $stmt = $this->conn->prepare("INSERT INTO Loans (ID_account, Amount_loan, Interest_rate, Type_loan, Date_open_loan, Date_close_loan, Monthly_payment, Having_loan)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "idssssdi",
            $data['ID_account'],
            $data['Amount_loan'],
            $data['Interest_rate'],
            $data['Type_loan'],
            $data['Date_open_loan'],
            $data['Date_close_loan'],
            $data['Monthly_payment'],
            $data['Having_loan']
        );
        return $stmt->execute();
    }

    // Получение всех кредитов клиента
    public function getAllByClientId($client_id) {
        $sql = "SELECT * FROM Loans WHERE ID_client = ? AND Having_loan = 1 ORDER BY Date_open_loan DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $loans = [];
        while ($row = $result->fetch_assoc()) {
            $loans[] = $row;
        }
        return $loans;
    }

    // Закрытие кредита
    public function closeLoan($loan_id) {
        $sql = "UPDATE Loans SET Having_loan = 0 WHERE ID_loan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $loan_id);
        return $stmt->execute();
    }

    // Получение информации о конкретном кредите
    public function getLoanById($loan_id) {
        $sql = "SELECT * FROM Loans WHERE ID_loan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function requestClosure($loan_id, $client_id) {
        $sql = "INSERT INTO Loan_Closure_Requests (ID_loan, ID_client) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $loan_id, $client_id);
        return $stmt->execute();
    }

    // Получение запросов на закрытие кредита для клиента
    public function getClosureRequestsByClientId($client_id) {
        $sql = "SELECT lcr.ID_closure_request, l.Type_loan, lcr.Status 
                FROM Loan_Closure_Requests lcr
                JOIN Loans l ON lcr.ID_loan = l.ID_loan
                WHERE lcr.ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        return $requests;
    }
}
?>

<?php
// Класс работы с кредитами
class LoanApplication {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Получение всех заявок
    public function getAllByClientId($client_id) {
        $sql = "SELECT * FROM Loan_Applications WHERE ID_client = ? ORDER BY Date_applied DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $applications = [];
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }

        return $applications;
    }

    // Удаление заявки
    public function deleteById($application_id, $client_id) {
        $sql = "DELETE FROM Loan_Applications WHERE ID_application = ? AND ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $application_id, $client_id);
        return $stmt->execute();
    }

    // Новая заявка
    public function create($client_id, $type_loan, $amount_loan) {
        $status = 'в стадии рассмотрения';
        $sql = "INSERT INTO Loan_Applications (ID_client, Type_loan, Amount_loan, Status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isds", $client_id, $type_loan, $amount_loan, $status);
        return $stmt->execute();
    }

    public function getById($application_id, $client_id) {
        $sql = "SELECT * FROM Loan_Applications WHERE ID_application = ? AND ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $application_id, $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function update($application_id, $client_id, $type_loan, $amount_loan) {
        $sql = "UPDATE Loan_Applications SET Type_loan = ?, Amount_loan = ? WHERE ID_application = ? AND ID_client = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdii", $type_loan, $amount_loan, $application_id, $client_id);
        return $stmt->execute();
    }
}
?>

<?php
// Класс транзакций
class Transaction {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    //все транзакций клиента
    public function getAllByClientId($client_id) {
        $sql_accounts = "SELECT ID_accounts FROM Accounts WHERE ID_client=? AND Active_account=1";
        $stmt_accounts = $this->conn->prepare($sql_accounts);
        $stmt_accounts->bind_param("i", $client_id);
        $stmt_accounts->execute();
        $result_accounts = $stmt_accounts->get_result();

        $account_ids = [];
        while ($row = $result_accounts->fetch_assoc()) {
            $account_ids[] = $row['ID_accounts'];
        }

        if (empty($account_ids)) {
            return [];
        }

        $account_ids_str = implode(',', array_fill(0, count($account_ids), '?'));

        $sql_transactions = "SELECT * FROM Transactions 
                             WHERE ID_sending_account IN ($account_ids_str) 
                                OR ID_recipient_account IN ($account_ids_str) 
                             ORDER BY Date_transaction DESC";

        $types = str_repeat('i', count($account_ids));
        $stmt_transactions = $this->conn->prepare($sql_transactions);
        $stmt_transactions->bind_param($types . $types, ...array_merge($account_ids, $account_ids));
        $stmt_transactions->execute();
        $result_transactions = $stmt_transactions->get_result();

        $transactions = [];
        while ($row = $result_transactions->fetch_assoc()) {
            $transactions[] = $row;
        }

        return $transactions;
    }

    public function getClientName($account_id) {
        $sql_account = "SELECT ID_client FROM Accounts WHERE ID_accounts=?";
        $stmt_account = $this->conn->prepare($sql_account);
        $stmt_account->bind_param("i", $account_id);
        $stmt_account->execute();
        $result_account = $stmt_account->get_result();

        if ($result_account->num_rows > 0) {
            $row_account = $result_account->fetch_assoc();
            $client_id = $row_account['ID_client'];

            $sql_client = "SELECT First_name, Last_name, Patronymic FROM Clients WHERE ID_client=?";
            $stmt_client = $this->conn->prepare($sql_client);
            $stmt_client->bind_param("i", $client_id);
            $stmt_client->execute();
            $result_client = $stmt_client->get_result();

            if ($result_client->num_rows > 0) {
                $row_client = $result_client->fetch_assoc();
                $first_name = $row_client['First_name'];
                $last_name = $row_client['Last_name'];
                $patronymic = $row_client['Patronymic'];
                return $last_name . ' ' . mb_substr($first_name, 0, 1) . '.' . mb_substr($patronymic, 0, 1) . '.';
            }
        }
        return 'Неизвестный клиент';
    }

    public function getAccountCurrency($account_id) {
        $sql_account = "SELECT Currency FROM Accounts WHERE ID_accounts=?";
        $stmt_account = $this->conn->prepare($sql_account);
        $stmt_account->bind_param("i", $account_id);
        $stmt_account->execute();
        $result_account = $stmt_account->get_result();

        if ($result_account->num_rows > 0) {
            $row_account = $result_account->fetch_assoc();
            return $row_account['Currency'];
        }
        return 'Неизвестная валюта';
    }

    // Новая транзакция
    public function create($recipient_account_id, $sending_account_id, $type_transaction, $amount_transaction) {
        $sql = "INSERT INTO Transactions (ID_recipient_account, ID_sending_account, Type_transaction, Amount_transaction, Date_transaction)
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisd", $recipient_account_id, $sending_account_id, $type_transaction, $amount_transaction);

        return $stmt->execute();
    }
}
?>

<?php
// Класс персонала
class Staff {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Авторизация сотрудника
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT ID_staff, Password FROM staff WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $staff = $result->fetch_assoc();
            if (password_verify($password, $staff['Password'])) {
                return $staff['ID_staff'];
            }
        }
        return false;
    }

    // Получение информации о сотруднике
    public function getStaffInfo($staff_id) {
        $stmt = $this->conn->prepare("SELECT First_name, Last_name, Patronymic, Post FROM staff WHERE ID_staff = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // Проверка роли сотрудника
    public function isCreditDepartmentStaff($staff_id) {
        $stmt = $this->conn->prepare("SELECT Post FROM staff WHERE ID_staff = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['Post'] === 'сотрудник кредитного отдела';
        }
        return false;
    }

    public function isAdmin($staff_id) {
        $sql = "SELECT Post FROM Staff WHERE ID_staff = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['Post'] === 'администратор';
        }
        return false;
    }
}
?>

Отдельный класс для взаимодействия с базой данных

<?php
// Класс подключения к БД
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "bank - kuzbank";
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Ошибка подключения: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>


Описание функционала программы
![image](https://github.com/user-attachments/assets/881b048c-de4f-405b-8344-d1f628e685a9)
Рис. 1 – Меню приложения


Вход со стороны клиента
![image](https://github.com/user-attachments/assets/19bd74cb-4952-49a5-aa01-82270fb8bfc3)
Рис. 2 – Войти за клиента

Для входа в систему необходимо ввести email и пароль, для удобства тестирования есть выпадающий список с клиентами из базы.

![image](https://github.com/user-attachments/assets/47f63de5-fe91-4c97-aa99-fa1c4d712662)
Рис. 3 – Личный кабинет клиента


![image](https://github.com/user-attachments/assets/99c986b4-2f97-46ab-ad12-0cc126eeafbf)
Рис. 4 – Страница счетов и переводов
На странице предоставлена информация о существующих счетах клиента. Можно осуществить перевод средств другому клиенту, зарегистрированному в системе, указав его телефон и сумму перевода.
Можно открыть новый счет, где из выпадающего списка необходимо выбрать его тип, также валюту, и начальный баланс.
За определенный интервал времени можно посмотреть историю транзакций.

![image](https://github.com/user-attachments/assets/1d5c383b-3c4d-42bc-bd75-eb35fe02a9a1)
Рис. 5 - Отчет по истории транзакций у клиента. Можно скачать в Word или в Excel


![image](https://github.com/user-attachments/assets/54ad950b-90f1-495e-8b81-df10e6c97194)
Рис. 6 - Скачанный отчет по истории транзакций у клиента


![image](https://github.com/user-attachments/assets/7c5a7951-35b5-41b1-b812-d3e0256c3395)
Рис. 7 – История транзакций в личном кабинете

![image](https://github.com/user-attachments/assets/25cd50c9-82f8-4133-9aa3-c1c88f6400c7)
Рис. 8 – Пункт меню подать заявку на кредит
Необходимо выбрать тип кредита и его сумму. После отправки заявки со стороны работников банка выносится решение одобрен кредит или нет.


Вход со стороны сотрудника
![image](https://github.com/user-attachments/assets/569267eb-1efd-4a5c-8fc6-a3fc7588e056)
Рис. 9 – Вход для сотрудников
С сотрудниками в случае входа та же ситуация что и для клиентов.

![image](https://github.com/user-attachments/assets/5a5b8603-e4fd-4901-9ce0-6afcfa556544)
Рис. 10 – Панель управления сотрудников

![image](https://github.com/user-attachments/assets/248f86f6-8fbf-4e60-81a5-83203c431064)
Рис. 11 – Заявки на кредит 
В этом пункте сотрудник может видеть поступившую заявку на кредит, где он выносит решение, в случае одобрения кредита указывает процентную ставку, когда кредит должен быть выплачен.

![image](https://github.com/user-attachments/assets/882c708c-3b01-4042-a035-132d15065e4b)
Рис. 12 – Подозрительные транзакции
В данном пункте можно увидеть одну подозрительную транзакцию, она появилась в результате того что обычно клиент банка выполнял переводы суммами не превышающими кратно предыдущие, и в конце концов выполнил перевод на 40000 рублей что оказалось сильно выше прошлых переводов.

Также можно применить фильтры и выполнить выгрузку данных в удобном формате.
![image](https://github.com/user-attachments/assets/b9f1ba19-86fa-4083-8a26-3061ec87eebb)

Рис. 13 – Отчёт по прибыли по типам кредито у сотрудников. Можно скачать в Word или в Excel

![image](https://github.com/user-attachments/assets/d48925d0-25d4-42b7-81ca-68881dc4c204)
Рис. 14 - Скачанный отчет в Word по прибыли по типам кредитов.


![image](https://github.com/user-attachments/assets/c62a9406-66c9-43c2-bd42-b7b954843d7d)
Рис. 15 – Поле регистрации для новых клиентов
Функционал включает:
•	Вход клиентов и сотрудников.
•	Управление счетами (просмотр, создание, переводы).
•	Оформление и управление кредитами.
•	Анализ транзакций на предмет подозрительных операций.
•	Генерацию отчётов.
Приложение предоставляет интерфейс для клиентов и сотрудников, обеспечивая удобный доступ к банковским операциям.


![image](https://github.com/user-attachments/assets/2bd8ed27-0cfe-46fe-a44c-15fbe19e6ad5)
Рис. 16 - Пароли в зашифрованном виде в базе данных у клиентов 


![image](https://github.com/user-attachments/assets/f5bc36f1-33b3-4133-b199-7b4af5d8a448)
Рис. 17 - Пароли в зашифрованном виде в базе данных у сотрудников




-----------------------------------------------------------------------------------
Безопасность: 

1) Проверка пароля у пользователя при входе в систему:
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Client.php';

$database = new Database();
$conn = $database->getConnection();

$client = new Client($conn);

$query = "SELECT First_name, Last_name, Email, Password, Plain_password FROM Clients";
$stmt = $conn->prepare($query);
$stmt->execute();

$result = $stmt->get_result();
$clients = $result->fetch_all(MYSQLI_ASSOC);

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user_id = $client->login($email, $password);
    if ($user_id) {
        $_SESSION['user_id'] = $user_id;
        header("Location: dashboard.php");
        exit;
    } else {
        $message = "Неверный email или пароль.";
    }
}
?>


2) ![image](https://github.com/user-attachments/assets/3d46d80a-159c-41ee-8361-c4f2ea617e96)
Рис. 18 - проверка при регистрации клиента


3) Проверка при входе у сотрудников:
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


4) В сотрудниках используется следующая реализация разделения "ролей": если у сотрудника роль "сотрудник кредитного отдела", то он видит следующую информацию - может одобрять заявки на открытие/закрытие кредитов; может смотреть отчеты:
![image](https://github.com/user-attachments/assets/31615d6c-8f55-4f54-b42f-e378e0c95092)

Если у сотрудника роль "администратор", то он видит следующую информацию - у него уже отображается новая кнопка "отчет по сотрудникам"; перейти по кнопке "заявки по кредитам" сотрудник с данной ролью НЕ может - открывать и закрывать кредиты не может:
![image](https://github.com/user-attachments/assets/63e4bd72-b497-4c29-9a89-3d7cc0b7ff4d)

Если у сотрудника роль "менеджер" или "кассир", то он видит следующую информацию - "отчет по подозрительным транзакциям"; "отчет по прибыли по типам кредитов"; одобрить заявку на кредит или закрыть кредит сотрудник с данной ролью НЕ может:
![image](https://github.com/user-attachments/assets/e26ae10c-45eb-46f5-a26d-41318d900514)




