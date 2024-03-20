<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "zadania";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit']) && $_POST['submit'] == 'Dodaj zadanie') {
    $tytul = $_POST['tytul'];
    $opis = isset($_POST['opis']) ? $_POST['opis'] : "";
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO zadania (tytul, opis, id_uzytkownika) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $tytul, $opis, $user_id);
    $stmt->execute();
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit']) && ($_POST['submit'] == 'Oznacz jako wykonane' || $_POST['submit'] == 'Oznacz jako niewykonane')) {
    $zadanie_id = $_POST['zadanie_id'];
    $status = ($_POST['submit'] == 'Oznacz jako wykonane') ? 1 : 0;
    $sql = "UPDATE zadania SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $status, $zadanie_id);
    $stmt->execute();
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: logowanie.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit']) && $_POST['submit'] == 'Potwierdź zmiany') {
    $zadanie_id = $_POST['zadanie_id'];
    $nowy_opis = $_POST['nowy_opis'];
    $sql = "UPDATE zadania SET opis = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nowy_opis, $zadanie_id);
    $stmt->execute();
    header("Location: {$_SERVER['PHP_SELF']}?task_id=$zadanie_id");
    exit();
}

// Usuwanie zadania z potwierdzeniem
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit']) && $_POST['submit'] == 'Usuń zadanie') {
    $zadanie_id = $_POST['zadanie_id'];
    echo "<script>
            if(confirm('Czy na pewno chcesz usunąć to zadanie?')) {
                window.location.href = '{$_SERVER['PHP_SELF']}?delete_id=$zadanie_id';
            }
        </script>";
}

// Potwierdzenie usunięcia zadania
if (isset($_GET['delete_id'])) {
    $zadanie_id = $_GET['delete_id'];
    $sql = "DELETE FROM zadania WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $zadanie_id);
    $stmt->execute();
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projekty</title>
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat';
            background-color: #f4f4f4;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 200px;
            background-color: #333;
            color: #fff;
            padding: 20px;
            overflow-x: hidden;
            transition: 0.5s;
        }
        
        .sidebar h2 {
            margin-bottom: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 16px;
            color: #fff;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background-color: #ddd;
            color: #333;
        }
                .sidebar_bottom {
            position: absolute;
            bottom: 50px;
            width: 100%;
        }
        .main {
            margin-left: 220px;
            padding: 90px;
            text-align: center;
        }
        input[type="text"],
        input[type="submit"] {
            margin-right: 10px;
            margin-bottom: 10px;
            padding: 5px;
            font-size: 16px;
            background-color: #333;
            color:white;
            border-radius: 5px;
            width:13rem;
        }
        input[name="potwierdz"] {background-color: black;}
        
        .task {
            background-color: #fff;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            
        }
        .task h3 {
            margin-top: 0;
        }
        .task p {
            margin-bottom: 10px;
            
        }
        .task-status {
            float: right;
        }
        
        .menu-toggle {
            position: absolute;
            top: 20px;
            left: 20px;
            cursor: pointer;
            z-index: 1000;
        }
        .menu-toggle .bar {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #fff;
            margin: 5px 0;
            transition: 0.4s;
        }
        .add-task input[type="text"]::placeholder,
        .add-task input[type="submit"]::placeholder {
            color: white; 
        }
        
        @media screen and (max-width: 600px) {
            
            .sidebar {
                display: none;
            }
            .main {
                
                transition: margin-left 0.5s;
                width: 90%; 
                margin-left: 0; 
                
            }
            
            .sidebar.active {
                display: block;
            }
            textarea {
                width: calc(100% - 20px); 
            }
        }
        
        
    </style>
</head>
<body>
    <!-- Zwijane menu -->
    <div class="menu-toggle" onclick="toggleSidebar()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </div>

    <!-- Pasek(Sidebar) -->
    <div class="sidebar"><br>
        <h2>Projekty</h2>
        <?php
        $sql = "SELECT * FROM zadania WHERE id_uzytkownika = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<a href='{$_SERVER['PHP_SELF']}?task_id=" . $row['id'] . "'>" . $row['tytul'] . "<span class='task-status'>" . ($row['status'] == 1 ? "<span style='color:green;'>&#10004;</span>" : "<span style='color:red;'>&#10006;</span>") . "</span></a>";
            }
        } else {
            echo "Brak zadań";
        }
        ?>
        <div class="sidebar_bottom">
            <?php
        if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        echo "<div class='user-info'>$username</div>";
    }    ?>
        <a href="logowanie.php">Wyloguj się</a></div>
    </div>

    <!-- glowna(main) -->
    <div class="main">
        <div class="add-task">
            <form action="" method="POST">
                <input type="text" name="tytul" placeholder="Tytuł zadania" required>
                <input type="text" name="opis" placeholder="Opis zadania">
                <input type="submit" name="submit" value="Dodaj zadanie">
            </form>
        </div>
        <?php
        if (isset($_GET['task_id'])) {
            $task_id = $_GET['task_id'];
            $sql = "SELECT * FROM zadania WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $task_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<div class='task'>";
                echo "<h3>" . $row['tytul'] . "</h3>";
                // opis
                echo "<form action='' method='POST'>";
                echo "<textarea name='nowy_opis' rows='4' cols='50'>" . $row['opis'] . "</textarea><br>";
                echo "<input type='hidden' name='zadanie_id' value='" . $row['id'] . "'>";
                echo "<input type='submit' name='submit' value='Potwierdź zmiany'>";
                echo "</form>";
                echo "<form action='' method='POST'>";
                echo "<input type='hidden' name='zadanie_id' value='" . $row['id'] . "'>";
                if ($row['status'] == 1) {
                    echo "<input type='submit' name='submit' value='Oznacz jako niewykonane'>";
                } else {
                    echo "<input type='submit' name='submit' value='Oznacz jako wykonane'>";
                }
                echo "</form>";
                echo "<form action='' method='POST'>";
                echo "<input type='hidden' name='zadanie_id' value='" . $row['id'] . "'>";
                echo "<input type='submit' name='submit' value='Usuń zadanie'>";
                echo "</form>";
                echo "</div>";
            } else {
                echo "Nie znaleziono zadania.";
            }
        }
        ?>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            var main = document.querySelector('.main');
            sidebar.classList.toggle('active');
            if (sidebar.classList.contains('active')) {
                main.style.marginLeft = '0';
            } else {
                main.style.marginLeft = '';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>

