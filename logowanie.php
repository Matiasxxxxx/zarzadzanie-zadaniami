<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logowanie</title>
<style>
    form {
        width: 300px;
        margin: 0 auto;
        padding: 20px;
        background: #f4f7f6;
        border-radius: 10px;
    }
    input[type="text"],
    input[type="password"] {
        width: 90%;
        padding: 10px;
        margin: 5px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    input[type="submit"] {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border: none;
        border-radius: 5px;
        background: #4caf50;
        color: white;
        cursor: pointer;
        background-color: #333;
    }
    .error {
        color: red;
        margin-top: 5px;
    }
</style>
</head>
<body>
<form action="logowanie.php" method="post">
    <h2>Logowanie</h2>
    <input type="text" name="username" placeholder="Nazwa użytkownika" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"><br>
    <?php if(isset($username_error)) echo '<p class="error">'.$username_error.'</p>'; ?>
    <input type="password" name="password" placeholder="Hasło" required><br>
    <?php if(isset($password_error)) echo '<p class="error">'.$password_error.'</p>'; ?>
    <input type="submit" value="Zaloguj">
    <p>Nie masz jeszcze konta? <a href="rejestracja.php">Zarejestruj się</a></p>


<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Połączenie z bazą danych
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "zadania";

    $conn = new mysqli($host, $user, $pass, $dbname);

    // Sprawdzanie połączenia
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM uzytkownicy WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Prawidłowe uwierzytelnienie, przypisanie id użytkownika do sesji
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("location: index.php");
            exit();
        } else {
            $password_error = "Błędne hasło!";
            echo "Błędne hasło!";
        }
    } else {
        $username_error = "Nieprawidłowa nazwa użytkownika!";
        echo "Nieprawidłowa nazwa użytkownika!";
    }
    $stmt->close();
    $conn->close();
}
?></form>
</body>
</html>
