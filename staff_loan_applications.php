<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/LoanApplication.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$staff_id = $_SESSION['staff_id'];

$sql_staff = "SELECT Post FROM Staff WHERE ID_staff = ?";
$stmt_staff = $conn->prepare($sql_staff);
$stmt_staff->bind_param("i", $staff_id);
$stmt_staff->execute();
$result_staff = $stmt_staff->get_result();

if ($result_staff->num_rows === 0 || $result_staff->fetch_assoc()['Post'] !== 'сотрудник кредитного отдела') {
    header("Location: staff_dashboard.php");
    exit;
}

$loanApplication = new LoanApplication($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = intval($_POST['application_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        if (!isset($_POST['interest_rate']) || !isset($_POST['date_close_loan'])) {
            echo "<script>alert('Пожалуйста, заполните все поля для одобрения заявки.'); window.location.href='staff_loan_applications.php';</script>";
            exit;
        }

        $sql_application = "SELECT ID_client, Type_loan, Amount_loan FROM Loan_Applications WHERE ID_application = ?";
        $stmt_application = $conn->prepare($sql_application);
        $stmt_application->bind_param("i", $application_id);
        $stmt_application->execute();
        $result_application = $stmt_application->get_result();

        if ($result_application->num_rows > 0) {
            $row_application = $result_application->fetch_assoc();

            $interest_rate = floatval($_POST['interest_rate']);
            $date_close_loan = $_POST['date_close_loan'];
            $amount_loan = $row_application['Amount_loan'];

            $loan_term_months = (strtotime($date_close_loan) - strtotime(date('Y-m-d'))) / (60 * 60 * 24 * 30);
            $monthly_interest_rate = $interest_rate / 100 / 12;
            $monthly_payment = ($amount_loan * $monthly_interest_rate * pow(1 + $monthly_interest_rate, $loan_term_months)) /
                (pow(1 + $monthly_interest_rate, $loan_term_months) - 1);

            $stmt_update = $conn->prepare("UPDATE Loan_Applications SET Status = 'одобрен' WHERE ID_application = ?");
            $stmt_update->bind_param("i", $application_id);
            $stmt_update->execute();
            $stmt_update->close();

            $stmt_loans = $conn->prepare("INSERT INTO Loans (ID_staff, ID_client, Amount_loan, Interest_rate, Type_loan, Date_open_loan, Date_close_loan, Monthly_payment, Having_loan)
                                          VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, 1)");
            $stmt_loans->bind_param("iiidssd", $staff_id, $row_application['ID_client'], $amount_loan, $interest_rate, $row_application['Type_loan'], $date_close_loan, $monthly_payment);
            $stmt_loans->execute();
            $stmt_loans->close();
        }
    } elseif ($action === 'reject') {
        $stmt_reject = $conn->prepare("UPDATE Loan_Applications SET Status = 'rejected' WHERE ID_application = ?");
        $stmt_reject->bind_param("i", $application_id);
        $stmt_reject->execute();
        $stmt_reject->close();
    }

    echo "<script>alert('Действие выполнено!'); window.location.href='staff_loan_applications.php';</script>";
}

$sql = "SELECT la.ID_application, c.First_name, c.Last_name, la.Type_loan, la.Amount_loan, la.Status 
        FROM Loan_Applications la
        JOIN Clients c ON la.ID_client = c.ID_client";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявки на кредит</title>
    <link rel="stylesheet" href="styli.css">
    <script>
        function hideApproveFields() {
            document.getElementById('approve-fields').style.display = 'none';
 
            const interestRateField = document.querySelector('input[name="interest_rate"]');
            const dateCloseLoanField = document.querySelector('input[name="date_close_loan"]');
            if (interestRateField) interestRateField.removeAttribute('required');
            if (dateCloseLoanField) dateCloseLoanField.removeAttribute('required');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Заявки на кредит</h1>
        <a href="staff_dashboard.php" class="button">Назад в панель сотрудника</a><br>
        <a href="staff_logout.php" class="button">Выход</a><br>
        <table class="loan-applications-table">
            <thead>
                <tr>
                    <th>ID заявки</th>
                    <th>Клиент</th>
                    <th>Тип кредита</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['ID_application']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['First_name'] . ' ' . $row['Last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Type_loan']) . "</td>";
                        echo "<td>" . number_format($row['Amount_loan'], 2, '.', ' ') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "<td>";
                        if ($row['Status'] === 'в стадии рассмотрения') {
                            echo "<form action='' method='POST'>";
                            echo "<input type='hidden' name='application_id' value='" . htmlspecialchars($row['ID_application']) . "'>";

                            echo "<div id='approve-fields'>";
                            echo "<label for='interest_rate'>Процентная ставка:</label>";
                            echo "<input type='number' name='interest_rate' step='0.01' required><br>";
                            echo "<label for='date_close_loan'>Дата закрытия кредита:</label>";
                            echo "<input type='date' name='date_close_loan' required><br>";
                            echo "</div>";

                            echo "<button type='submit' name='action' value='approve' class='button'>Одобрить</button>";
                            echo "<button type='submit' name='action' value='reject' class='button' onclick='hideApproveFields()'>Отклонить</button>";
                            echo "</form>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Нет заявок.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>