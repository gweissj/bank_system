-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3308
-- Время создания: Апр 29 2025 г., 10:29
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `bank - kuzbank`
--
CREATE DATABASE IF NOT EXISTS `bankkuzbank` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bankkuzbank`;

-- --------------------------------------------------------

--
-- Структура таблицы `accounts`
--

CREATE TABLE `Accounts` (
  `ID_accounts` int(11) NOT NULL,
  `ID_client` int(11) NOT NULL,
  `Account_type` varchar(64) NOT NULL,
  `Balance` decimal(9,2) NOT NULL DEFAULT 0.00,
  `Opened_date` date NOT NULL,
  `Active_account` bit(1) NOT NULL,
  `Currency` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `accounts`
--

INSERT INTO `Accounts` (`ID_accounts`, `ID_client`, `Account_type`, `Balance`, `Opened_date`, `Active_account`, `Currency`) VALUES
(1, 1, 'депозитный', 150500.50, '2014-03-11', b'1', 'рубль'),
(2, 2, 'кредитный', 49500.75, '2015-06-20', b'1', 'доллар'),
(3, 3, 'дебетовый', 200000.00, '2016-09-05', b'1', 'евро'),
(4, 4, 'инвестиционный', 750000.25, '2017-12-14', b'1', 'юани'),
(5, 5, 'депозитный', 100050.80, '2018-02-28', b'1', 'рубль'),
(6, 6, 'кредитный', 30000.60, '2019-07-19', b'1', 'доллар'),
(7, 7, 'дебетовый', 120000.00, '2020-04-23', b'1', 'евро'),
(8, 8, 'инвестиционный', 602450.90, '2021-11-10', b'1', 'юани'),
(9, 9, 'депозитный', 90000.45, '2022-05-15', b'1', 'рубль'),
(10, 10, 'кредитный', 45000.70, '2023-08-08', b'1', 'доллар'),
(11, 2, 'дебетовый', 22500.00, '2025-03-05', b'1', 'рубль'),
(12, 12, 'дебетовый', 5000.00, '2025-03-05', b'1', 'рубль'),
(13, 2, 'дебетовый', 5000.00, '2025-03-18', b'1', 'евро'),
(14, 2, 'депозитный', 89000.00, '2025-03-25', b'1', 'юани');

-- --------------------------------------------------------

--
-- Структура таблицы `clients`
--

CREATE TABLE `Clients` (
  `ID_client` int(11) NOT NULL,
  `First_name` varchar(64) NOT NULL,
  `Last_name` varchar(64) NOT NULL,
  `Patronymic` varchar(64) DEFAULT NULL,
  `Date_of_birth` date NOT NULL,
  `Phone_number` varchar(64) DEFAULT NULL,
  `Passport_number` varchar(64) DEFAULT NULL,
  `Email` varchar(64) DEFAULT NULL,
  `Gender` bit(1) NOT NULL,
  `Password` varchar(64) DEFAULT NULL,
  `Plain_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `clients`
--

INSERT INTO `Clients` (`ID_client`, `First_name`, `Last_name`, `Patronymic`, `Date_of_birth`, `Phone_number`, `Passport_number`, `Email`, `Gender`, `Password`, `Plain_password`) VALUES
(1, 'Александр', 'Иванов', 'Сергеевич', '1998-05-12', '89638473689', '3819048473', 'alex_ivanov@mail.ru', b'1', '$2y$10$9.7O0v27PpW3a6rEfhJrmO1rwxTSullI/tuam4oErKXX7jlD7v2jq', '372839'),
(2, 'Мария', 'Петрова', 'Александровна', '2001-08-24', '+79586493792', '1458048474', 'maria_pet@yandex.ru', b'0', '$2y$10$ghnmu0X0Eq3EMVAKbqggzeP.tawOMs2DJEJN.Bkpfc1gXJWQVmibC', '273868'),
(3, 'Дмитрий', 'Смирнов', 'Игоревич', '1995-12-15', '89776543210', '1921048475', 'd.smirnov@gmail.com', b'1', '$2y$10$yB0kNR8z.ZQbfJ9qS2/YZuv0YXn1DlOVm2d6LK7jCVwHWokzx78G2', '278h79'),
(4, 'Анна', 'Козлова', 'Владимировна', '1997-04-03', '+79876543211', '3819048476', 'anna_koz@mail.ru', b'0', '$2y$10$3IAhQWJFdddJCWU/Xy37..YwpCsXFCfAHaFNXhMxz9jq3TpKZTq3G', 'd7y837'),
(5, 'Иван', 'Сидоров', 'Павлович', '2000-06-30', '89631234567', '2317048477', 'ivan_sid@yandex.ru', b'1', '$2y$10$uzPJiVJuBg.9d0FN9CI0f.ylezN8vmnjVfSeWUmXEttdqP42dfm1m', '686436'),
(6, 'Екатерина', 'Федорова', 'Михайловна', '1996-11-21', '+79998887766', '3819048478', 'katya_fed@gmail.com', b'0', '$2y$10$JLuI0cscJehi.Ipz/DJIzO4GWw8kS4B4/SbQSvRT7msFqYIMvXl4a', 'fy78n3'),
(7, 'Сергей', 'Морозов', 'Анатольевич', '1999-07-19', '89556667788', '3417048479', 'serg_morozov@mail.ru', b'1', '$2y$10$2Rk4EE.320gk/oZYvivQlepGxxCh1vPmZeS/oLm3K6pYt5SyR2L0O', '379h76'),
(8, 'Ольга', 'Васильева', 'Николаевна', '2002-09-05', '+79667778899', '3819048480', 'olga_vas@yandex.ru', b'0', '$2y$10$iRaIsF9KJitMQqodpPd1vu93M.LmxiZfUFX3yIAwyLd70EDeSVFU2', '372h8d'),
(9, 'Максим', 'Тихонов', 'Алексеевич', '1994-03-10', '89887776655', '1568048481', 'max_tikhonov@gmail.com', b'1', '$2y$10$.pjSb4SLm.gTbs159lgH6.iQ9JwETvRAiWjCEicWYDrOC1e84a50q', '3hu837'),
(10, 'Наталья', 'Зайцева', 'Григорьевна', '2003-01-27', '+79778889900', '3819048482', 'natasha_z@mail.ru', b'0', '$2y$10$1cZ0ElfRMrjx4IQHDxMcK.DOVRfZ4k0QPopWTtvaeY1/RXjRCLX7K', '372984'),
(12, 'Анатолий', 'Ногонков', 'Анатольевич', '1998-05-04', '8967523698', '3218565547', 'nogon62@gmail.com', b'1', '$2y$10$g2gRD1md9HvKEV0/SrhO7e/pdJqmpRQlIIs.jmeNCcKIU1M01mYBO', NULL),
(13, 'yugj', 'yughbv', 'dsgfgh', '1995-04-28', '89623684571', '3611458974', 'ygfh@gmail.com', b'1', '$2y$10$x5bUnZW8/xcXh70liBfYJ.uwooPoQA.PYw/NMlEWT9NrIkac8147m', NULL),
(15, 'Борис', 'Новиков', 'Иванович', '1994-07-28', '89564125587', '3212569811', 'nov52@gmail.com', b'1', '$2y$10$Pyem75qmC7S3an5TCpLwS.t0e58FZPE4bbB7058bIhFkwrklkuLnK', NULL),
(16, 'Анатолий', 'Ногонков', 'Анатольевич', '2008-04-22', '89756588848', '3218446698', 'aluewh@bk.ru', b'1', '$2y$10$Ajh36F/qoG/jxm/iX1Vukun0dpZ5XIbshtUgvD5LpaecCrit53WNq', '123456');

-- --------------------------------------------------------

--
-- Структура таблицы `loans`
--

CREATE TABLE `Loans` (
  `ID_loan` int(11) NOT NULL,
  `ID_staff` int(11) NOT NULL,
  `ID_client` int(11) NOT NULL,
  `Amount_loan` decimal(9,2) NOT NULL CHECK (`Amount_loan` > 0),
  `Interest_rate` decimal(9,2) NOT NULL CHECK (`Interest_rate` >= 0),
  `Type_loan` varchar(64) NOT NULL,
  `Date_open_loan` date NOT NULL,
  `Date_close_loan` date DEFAULT NULL,
  `Monthly_payment` double NOT NULL CHECK (`Monthly_payment` >= 0),
  `Having_loan` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `loans`
--

INSERT INTO `Loans` (`ID_loan`, `ID_staff`, `ID_client`, `Amount_loan`, `Interest_rate`, `Type_loan`, `Date_open_loan`, `Date_close_loan`, `Monthly_payment`, `Having_loan`) VALUES
(1, 1, 2, 150000.00, 15.00, 'автомобильный', '2015-06-20', '2025-06-20', 15000, b'0'),
(2, 2, 3, 100000.00, 10.00, 'ипотечный', '2019-07-19', '2029-07-19', 12000, b'1'),
(3, 3, 5, 75000.00, 12.00, 'образовательный', '2023-08-08', '2028-08-08', 7000, b'1'),
(4, 4, 7, 200000.00, 18.00, 'потребительский', '2015-06-21', '2025-06-21', 22000, b'1'),
(5, 5, 8, 50000.00, 14.00, 'автомобильный', '2019-07-20', '2024-07-20', 6000, b'1'),
(6, 6, 10, 120000.00, 16.50, 'ипотечный', '2023-08-09', '2033-08-09', 14000, b'1'),
(7, 8, 2, 60000.00, 9.00, 'образовательный', '2025-03-05', '2029-10-17', 1311.7155210920937, b'0'),
(8, 8, 2, 50000.00, 21.00, 'автомобильный', '2025-03-18', '2030-10-31', 1259.1134718277292, b'0'),
(9, 8, 2, 80000.00, 18.00, 'потребительский', '2025-03-18', '2030-06-12', 1958.1425145787682, b'1'),
(10, 4, 2, 9999999.99, 46.00, 'ипотечный', '2025-03-25', '2070-06-19', 958333.3342978752, b'1');

-- --------------------------------------------------------

--
-- Структура таблицы `loan_applications`
--

CREATE TABLE `Loan_Applications` (
  `ID_application` int(11) NOT NULL,
  `ID_client` int(11) NOT NULL,
  `Type_loan` varchar(50) NOT NULL,
  `Amount_loan` decimal(10,2) NOT NULL,
  `Status` enum('в стадии рассмотрения','одобрен','отклонен') DEFAULT 'в стадии рассмотрения',
  `Date_applied` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `loan_applications`
--

INSERT INTO `Loan_Applications` (`ID_application`, `ID_client`, `Type_loan`, `Amount_loan`, `Status`, `Date_applied`) VALUES
(1, 2, 'ипотечный', 450000.00, '', '2025-03-05 09:54:21'),
(2, 2, 'образовательный', 60000.00, 'одобрен', '2025-03-05 09:58:54'),
(3, 12, 'автомобильный', 750000.00, '', '2025-03-05 10:38:12'),
(5, 2, 'автомобильный', 50000.00, 'одобрен', '2025-03-18 15:32:35'),
(6, 2, 'потребительский', 80000.00, 'одобрен', '2025-03-18 15:40:12'),
(7, 2, 'ипотечный', 25000000.00, 'одобрен', '2025-03-25 13:22:35'),
(8, 2, 'автомобильный', 473982.00, '', '2025-03-25 13:26:15'),
(9, 2, 'Образовательный', 427832.00, '', '2025-03-25 13:26:24');

-- --------------------------------------------------------

--
-- Структура таблицы `loan_closure_requests`
--

CREATE TABLE `Loan_Closure_Requests` (
  `ID_closure_request` int(11) NOT NULL,
  `ID_loan` int(11) NOT NULL,
  `ID_client` int(11) NOT NULL,
  `Status` enum('в стадии рассмотрения','одобрен','отклонен') DEFAULT 'в стадии рассмотрения',
  `Date_requested` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `loan_closure_requests`
--

INSERT INTO `Loan_Closure_Requests` (`ID_closure_request`, `ID_loan`, `ID_client`, `Status`, `Date_requested`) VALUES
(1, 7, 2, 'отклонен', '2025-03-05 09:59:30'),
(2, 7, 2, 'одобрен', '2025-03-05 09:59:56'),
(3, 8, 2, 'отклонен', '2025-03-18 15:39:28'),
(4, 8, 2, 'отклонен', '2025-03-18 15:39:29'),
(5, 8, 2, 'одобрен', '2025-03-18 15:39:30'),
(6, 8, 2, 'одобрен', '2025-03-18 15:39:30'),
(7, 8, 2, 'одобрен', '2025-03-18 15:39:30'),
(8, 8, 2, 'одобрен', '2025-03-18 15:39:30'),
(9, 1, 2, 'одобрен', '2025-03-18 15:39:54'),
(10, 9, 2, 'отклонен', '2025-03-18 15:40:46');

-- --------------------------------------------------------

--
-- Структура таблицы `staff`
--

CREATE TABLE `Staff` (
  `ID_staff` int(11) NOT NULL,
  `First_name` varchar(64) NOT NULL,
  `Last_name` varchar(64) NOT NULL,
  `Patronymic` varchar(64) DEFAULT NULL,
  `Post` varchar(64) NOT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Plain_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `staff`
--

INSERT INTO `Staff` (`ID_staff`, `First_name`, `Last_name`, `Patronymic`, `Post`, `Password`, `Email`, `Plain_password`) VALUES
(1, 'Алексей', 'Иванов', 'Викторович', 'менеджер', '$2y$10$6mPVB3UfhqfNeenmYhckbOaZrHBtlx/zpk8UT8e55GfIBOG09sgu.', 'alexei_ivanov@example.com', 'password1'),
(2, 'Мария', 'Петрова', 'Сергеевна', 'администратор', '$2y$10$hYizHuEPBRI0tiuy2cSoK.VEjYulwYsskIAXKfDrKbNQa7yxeSFLS', 'maria_petrova@example.com', 'password2'),
(3, 'Дмитрий', 'Смирнов', 'Александрович', 'кассир', '$2y$10$DIcmrkq.oBBTG.xO9fTOT..KEY/0VKKozPhDqnBiTJs5Ae2tCXEV.', 'dmitriy_smirnov@example.com', 'password3'),
(4, 'Елена', 'Кузнецова', 'Ивановна', 'сотрудник кредитного отдела', '$2y$10$Ybx4l6wZESuirmNfJdFh9e6cPWaR1SP.IojWrdf3NfsIfXB2QZ8GG', 'elena_kuznetsova@example.com', 'password4'),
(5, 'Олег', 'Попов', 'Владимирович', 'менеджер', '$2y$10$9hMMKYdMd6z3R0hvA80q6uD4kLmtCmRlQTchOuMwkMCxT.d7//05.', 'oleg_popov@example.com', 'password5'),
(6, 'Анна', 'Сидорова', 'Анатольевна', 'кассир', '$2y$10$LIw9qIcsu/LtL4kElgu62etVRxMgwYUaQaxUy6GAacANERjS5938e', 'anna_sidrova@example.com', 'password6'),
(7, 'Виктор', 'Морозов', 'Петрович', 'сотрудник кредитного отдела', '$2y$10$XDyfFwETCO9SiqSarZjoQeup1PvJZ7zwOMLEbfA.OuPR6v1MAeukq', 'victor_morozov@example.com', 'password7'),
(8, 'Татьяна', 'Федорова', 'Григорьевна', 'сотрудник кредитного отдела', '$2y$10$/bBEN0wZoZqctXDxH1aEEubCadYOT4pWflNr6qnsLXhosGbVHDhnm', 'tatiana_fedorova@example.com', 'password8'),
(9, 'Сергей', 'Егоров', 'Михайлович', 'администратор', '$2y$10$JaTPPYDJb.ztK2GuEwNa6OEzwik/X3Db4GuCs22dw9uNY.HWt6OjC', 'sergey_egorov@example.com', 'password9'),
(10, 'Ирина', 'Васильева', 'Александровна', 'сотрудник кредитного отдела', '$2y$10$2EEAsO6p1NZVyIiCy7alj.tiF6kAUMfZE/W6MQUiO36h4PxQLTcr6', 'irina_vasileva@example.com', 'password10');

-- --------------------------------------------------------

--
-- Структура таблицы `transactions`
--

CREATE TABLE `Transactions` (
  `ID_transactions` int(11) NOT NULL,
  `ID_recipient_account` int(11) NOT NULL,
  `ID_sending_account` int(11) NOT NULL,
  `Type_transaction` varchar(64) NOT NULL,
  `Amount_transaction` decimal(9,2) NOT NULL CHECK (`Amount_transaction` > 0),
  `Date_transaction` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `transactions`
--

INSERT INTO `Transactions` (`ID_transactions`, `ID_recipient_account`, `ID_sending_account`, `Type_transaction`, `Amount_transaction`, `Date_transaction`) VALUES
(1, 1, 10, 'перевод', 5000.00, '2014-03-11'),
(2, 2, 9, 'пополнение', 20000.00, '2015-06-20'),
(3, 3, 8, 'снятие', 10000.00, '2016-09-05'),
(4, 4, 7, 'погашение', 15000.00, '2017-12-14'),
(5, 5, 6, 'перевод', 2500.50, '2018-02-28'),
(6, 8, 11, 'перевод', 1500.00, '2025-03-05'),
(7, 8, 2, 'перевод', 500.00, '2025-03-05'),
(8, 8, 11, 'перевод', 450.00, '2025-03-10'),
(9, 5, 11, 'перевод', 50.00, '2025-03-10'),
(10, 1, 11, 'перевод', 500.00, '2025-03-25');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `accounts`
--
ALTER TABLE `Accounts`
  ADD PRIMARY KEY (`ID_accounts`),
  ADD KEY `ID_client` (`ID_client`);

--
-- Индексы таблицы `clients`
--
ALTER TABLE `Clients`
  ADD PRIMARY KEY (`ID_client`),
  ADD UNIQUE KEY `Phone_number` (`Phone_number`),
  ADD UNIQUE KEY `Passport_number` (`Passport_number`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Индексы таблицы `loans`
--
ALTER TABLE `Loans`
  ADD PRIMARY KEY (`ID_loan`),
  ADD KEY `ID_staff` (`ID_staff`),
  ADD KEY `ID_client` (`ID_client`);

--
-- Индексы таблицы `loan_applications`
--
ALTER TABLE `Loan_Applications`
  ADD PRIMARY KEY (`ID_application`),
  ADD KEY `ID_client` (`ID_client`);

--
-- Индексы таблицы `loan_closure_requests`
--
ALTER TABLE `Loan_Closure_Requests`
  ADD PRIMARY KEY (`ID_closure_request`),
  ADD KEY `ID_loan` (`ID_loan`),
  ADD KEY `ID_client` (`ID_client`);

--
-- Индексы таблицы `staff`
--
ALTER TABLE `Staff`
  ADD PRIMARY KEY (`ID_staff`);

--
-- Индексы таблицы `transactions`
--
ALTER TABLE `Transactions`
  ADD PRIMARY KEY (`ID_transactions`),
  ADD KEY `ID_recipient_account` (`ID_recipient_account`),
  ADD KEY `ID_sending_account` (`ID_sending_account`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `accounts`
--
ALTER TABLE `Accounts`
  MODIFY `ID_accounts` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `clients`
--
ALTER TABLE `Clients`
  MODIFY `ID_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `loans`
--
ALTER TABLE `Loans`
  MODIFY `ID_loan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `loan_applications`
--
ALTER TABLE `Loan_Applications`
  MODIFY `ID_application` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `loan_closure_requests`
--
ALTER TABLE `Loan_Closure_Requests`
  MODIFY `ID_closure_request` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `staff`
--
ALTER TABLE `Staff`
  MODIFY `ID_staff` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `transactions`
--
ALTER TABLE `Transactions`
  MODIFY `ID_transactions` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `accounts`
--
ALTER TABLE `Accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`ID_client`) REFERENCES `clients` (`ID_client`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `loans`
--
ALTER TABLE `Loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`ID_staff`) REFERENCES `staff` (`ID_staff`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`ID_client`) REFERENCES `clients` (`ID_client`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `loan_applications`
--
ALTER TABLE `Loan_Applications`
  ADD CONSTRAINT `loan_applications_ibfk_1` FOREIGN KEY (`ID_client`) REFERENCES `clients` (`ID_client`);

--
-- Ограничения внешнего ключа таблицы `loan_closure_requests`
--
ALTER TABLE `Loan_Closure_Requests`
  ADD CONSTRAINT `loan_closure_requests_ibfk_1` FOREIGN KEY (`ID_loan`) REFERENCES `loans` (`ID_loan`),
  ADD CONSTRAINT `loan_closure_requests_ibfk_2` FOREIGN KEY (`ID_client`) REFERENCES `clients` (`ID_client`);

--
-- Ограничения внешнего ключа таблицы `transactions`
--
ALTER TABLE `Transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`ID_recipient_account`) REFERENCES `accounts` (`ID_accounts`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`ID_sending_account`) REFERENCES `accounts` (`ID_accounts`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
