Данные для входа в банковскую систему.
Клиенты:
Имя Фамилия - почта пароль

Александр Иванов -  alex_ivanov@mail.ru       372839
Мария Петрова -  maria_pet@yandex.ru          273868
Дмитрий Смирнов -  d.smirnov@gmail.com        278h79
Анна Козлова -  anna_koz@mail.ru              d7y837
Иван Сидоров -  ivan_sid@yandex.ru            686436
Екатерина Федорова -  katya_fed@gmail.com     fy78n3
Сергей Морозов -  serg_morozov@mail.ru        379h76
Ольга Васильева -  olga_vas@yandex.ru         372h8d
Максим Тихонов -  max_tikhonov@gmail.com      3hu837
Наталья Зайцева -  natasha_z@mail.ru          372984

---------

Сотрудники:
Имя Фамилия 'должность' - почта пароль

Алексей Иванов 'менеджер' -  alexei_ivanov@example.com        password1
Мария Петрова 'администратор' -  maria_petrova@example.com    password2
Дмитрий Смирнов 'кассир' -  dmitriy_smirnov@example.com       password3
Елена Кузнецова 'сотрудник кредитного отдела' -  elena_kuznetsova@example.com    password4
Олег Попов 'менеджер' -  oleg_popov@example.com       password5
Анна Сидорова 'кассир' -  anna_sidrova@example.com    password6
Виктор Морозов 'сотрудник кредитного отдела' -  victor_morozov@example.com        password7
Татьяна Федорова 'сотрудник кредитного отдела' -  tatiana_fedorova@example.com    password8
Сергей Егоров 'администратор' -  sergey_egorov@example.com    password9
Ирина Васильева 'сотрудник кредитного отдела' -  irina_vasileva@example.com       password10

-----------------------------------------------------------

Создание Docker-контейнера.

1) Dockerfile для веб-сервера:
   FROM php:8.2-apache
WORKDIR /var/www/html
RUN apt-get update && \
    apt-get install -y libzip-dev && \
    docker-php-ext-install zip mysqli && \
    docker-php-ext-enable zip
EXPOSE 80

--------------------------------------------------------

2) Конфигурация сервисов Compose; файл docker-compose.yml:
   version: '3.9'

services:
  apache:
    build: .
    container_name: apache
    volumes:
      - ./:/var/www/html
    ports:
      - "8080:80"
    depends_on:
      - mysql

  mysql:
    image: mysql:8.4
    container_name: mysql
    environment:
      MYSQL_DATABASE: bankkuzbank
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "3308:3308"
    volumes:
      - db_data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: phpmyadmin
    environment:
      PMA_HOST: mysql
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "8081:80"
    depends_on:
      - mysql
volumes:
  db_data:


Здесь для контейнера mysql указали, что доступ к БД bankkuzbank возможен из порта хоста 3308 под паролем корневого пользователя root.

--------------------------------------------------------------

3) Изменили файл для подключения к БД в файле проекта:
   <?php
class Database {
    private $servername = "mysql";
    private $username = "root";
    private $password = "root";
    private $dbname = "bankkuzbank"; 
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

---------------------------------------------------------------

4) Команда, которая использовалась для создания контейнера:
   docker-compose up -d

----------------------------------------------------------------

5) В результате выполнения команды по созданию контейнера - получилось следующее:
![image](https://github.com/user-attachments/assets/df86af5c-36eb-4107-b175-118c1894185b)
здесь можно увидеть, что был создан контейнер bank_system, который включает в себя 3 контейнера-сервиса: apache; phpmyadmin; mysql.

------------------------------------------------------------------

6) Для работы с контейнером перейдем по адресу: 
http://localhost:8080/

-------------------------------------------------------------------

7) Можно зайти как клиент:
   ![image](https://github.com/user-attachments/assets/e02d76d1-adfb-4f42-ab2f-e7a9f38c64dd)

   Или можно зайти как сотрудник:
   ![image](https://github.com/user-attachments/assets/5e0a3121-2c63-428b-bbfb-e2e3c93824e3)

   Также можно пройти регистрацию для клиента:
   ![image](https://github.com/user-attachments/assets/8ddb379d-e618-45ed-bf28-f76d0c43576f)

----------------------------------------------------------------------

   





   

