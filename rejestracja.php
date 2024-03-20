<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rejestracja</title>
<style>
    form {
        width: 300px;
        margin: 0 auto;
        padding: 20px;
        background: #f4f7f6;
        border-radius: 10px;
    }
    input[type="text"],
    input[type="password"],
    input[type="email"] {
        width: 90%;
        padding: 10px;
        margin: 5px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    input[type="submit"] {
        width: 97%;
        padding: 10px;
        margin-top: 10px;
        border: none;
        border-radius: 5px;
        background: #4caf50;
        color: white;
        cursor: pointer;
        background-color: #333;
    }
</style>
</head>
<body>
    <form action="rejestracja.php" method="post">
        <h2>Rejestracja</h2>
        <input type="text" name="username" placeholder="Nazwa użytkownika" value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" required><br>
        <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required><br>
        <input type="password" name="password" placeholder="Hasło" required><br>
        <input type="password" name="confirm_password" placeholder="Potwierdź hasło" required><br>
        <input type="submit" value="Zarejestruj">
        <p>Jesteś już zarejestrowany? <a href="logowanie.php">Zaloguj się</a></p>
    
    <?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "zadania";

// Połączenie z bazą danych
$conn = new mysqli($host, $user, $pass, $dbname);

// Sprawdzanie połączenia
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "Hasła nie są takie same!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Zapytanie SQL do wstawienia nowego użytkownika
        $stmt = $conn->prepare("INSERT INTO uzytkownicy (username, email, password) VALUES (?, ?, ?)");
        
        // Sprawdzenie, czy zapytanie się powiodło
        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                header("location: index.php");
                exit();
            } else {
                echo "Wystąpił błąd podczas rejestracji: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Błąd przygotowania zapytania: " . $conn->error;
        }
    }
}

// Zamknięcie połączenia z bazą danych
$conn->close();
?></form>



</body>
</html>
