<?php
session_start(); // Start der Sitzung

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: home.php');
    exit();
}

// No-Cache-Header
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$servername = "localhost";
$username = "Edan";
$password = "erendaniel";
$dbname = "TGMEATS";

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST["submit_support"])) {
        $email = $_SESSION["email"];
        $title = $_POST["title"];
        $text = $_POST["text"];

        // Überprüfen, ob die E-Mail in der Datenbank vorhanden ist
        $stmt = $conn->prepare("SELECT * FROM Benutzerdaten WHERE EMAIL = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Überprüfen, wie viele Support-Anfragen in den letzten 24 Stunden gestellt wurden
            $stmt = $conn->prepare("SELECT COUNT(*) FROM Support WHERE EMAIL = :email AND created_at > NOW() - INTERVAL 1 DAY");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count < 2) {
                // Weniger als 2 Anfragen in den letzten 24 Stunden, Support-Anfrage speichern
                $stmt = $conn->prepare("INSERT INTO Support (EMAIL, Titel, supporttext) VALUES (:email, :title, :supporttext)");
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":title", $title);
                $stmt->bindParam(":supporttext", $text);
                $stmt->execute();
                $success_message = "Support-Anfrage erfolgreich gesendet.";
            } else {
                // Mehr als 2 Anfragen in den letzten 24 Stunden
                $error_message = "Sie haben das Limit von 2 Support-Anfragen in 24 Stunden erreicht. Bitte versuchen Sie es später erneut.";
            }
        } else {
            $error_message = "Die eingegebene E-Mail-Adresse ist nicht in der Datenbank vorhanden.";
        }
    }

    if (isset($_POST["submit"])) {
        if ($is_logged_in) {
            $error_message = "Sie sind bereits angemeldet.";
        } else {
            $email = $_POST["email"];
            $password = $_POST["pw"];
            
            // Vorbereitete Anweisung, um den Benutzer anhand der E-Mail-Adresse abzurufen
            $stmt = $conn->prepare("SELECT * FROM Benutzerdaten WHERE EMAIL = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            // Abrufen des Benutzers
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Überprüfen, ob der Benutzer gefunden wurde
            if ($row) {
                // Passwortüberprüfung
                if (password_verify($password, $row["PASSWORT"])) { // Überprüfung des verschlüsselten Passworts
                    $_SESSION["email"] = $row["EMAIL"]; // Speichern der E-Mail-Adresse in der Sitzung
                    $_SESSION['logged_in'] = true;
                    header("Location: support.php"); // Weiterleitung zur support.php Seite
                    exit();
                } else {
                    $error_message = "Das eingegebene Passwort ist falsch.";
                }
            } else {
                $error_message = "Es wurde kein Benutzer mit dieser E-Mail-Adresse gefunden.";
            }
        }
    }

    if (isset($_POST["registrieren"])) {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['passwort'])) {
            $email = htmlspecialchars($_POST['email']);
            $passwort = htmlspecialchars($_POST['passwort']);
        
            try {
                if (!empty($email) && !empty($passwort)) {
                    // Überprüfen, ob die E-Mail bereits in der Datenbank vorhanden ist
                    $checkEmailQuery = "SELECT * FROM Benutzerdaten WHERE EMAIL = :email";
                    $stmt = $conn->prepare($checkEmailQuery);
                    $stmt->bindParam(":email", $email);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
                    if ($result) {
                        // Wenn die E-Mail bereits vorhanden ist, geben Sie eine entsprechende Fehlermeldung aus
                        $emailexistiert = "Die eingegebene E-Mail-Adresse existiert bereits.";
                    } else {
                        // Wenn die E-Mail nicht vorhanden ist, fügen Sie den Benutzer zur Datenbank hinzu
                        $stmt = $conn->prepare("INSERT INTO Benutzerdaten (EMAIL, PASSWORT) VALUES (:email, :passwort)");
                        $stmt->bindParam(":email", $email);
                        $hash = password_hash($passwort, PASSWORD_BCRYPT);
                        $stmt->bindParam(":passwort", $hash);
                        $stmt->execute();
                    }
                } else {
                    $fehler = "Bitte geben Sie eine gültige E-Mail-Adresse und ein Passwort ein.";
                }
            } catch (PDOException $e) {
                echo "Fehler: " . $e->getMessage();
            }
        }
    }

    if (isset($_POST["abmelden"])) {
        $_SESSION = array();
        session_destroy();
        header("Location: home.php");
        exit();
    }
} catch (PDOException $e) {
    $error_message = "Verbindung fehlgeschlagen: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - TGM EATS</title>
    
    <script>
        // Seite neu laden, wenn sie aus dem Cache geladen wird
        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.reload();
        }

        function checkemail() {
            var email = document.getElementById("email").value;
            var errorDisplay4 = document.getElementById("errorDisplaymail");
            var correctemail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

            if (email.match(correctemail)) {
                errorDisplay4.innerHTML = "";
            } else {
                errorDisplay4.innerHTML = "Falsche E-Mail-Adresse!";
                errorDisplay4.style.color = "red";
            }
        }
    </script>
    
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <a class="navbar-brand" href="#">
            <img src="images/TGM EATS Logo - White Text.png" alt="TGM EATS Logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if ($is_logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="foodspots.php">Foodspot</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="support.php">Support</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="foodspots.php">Foodspot</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="about_us.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="impressum.php">Impressum</a>
                </li>
                <?php if ($is_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <form method="post" action="">
                                <button type="submit" class="dropdown-item" name="abmelden">Abmelden</button>
                            </form>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="modal" data-target="#loginModal">
                            <i class="fas fa-user"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    
    <div class="container mt-5">
        <?php if ($is_logged_in): ?>
            <div class="support-box">
                <h2>Support-Anfrage</h2>
                <form method="post" action="support.php">
                    <div class="form-group">
                        <label for="title">Titel:</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="text">Support-Text:</label>
                        <textarea class="form-control" id="text" name="text" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit_support">Absenden</button>
                    <?php
                        if (isset($error_message)) {
                            echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                        }
                        if (isset($success_message)) {
                            echo '<div class="alert alert-success mt-3">' . $success_message . '</div>';
                        }
                    ?>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                Bitte melden Sie sich an, um eine Support-Anfrage zu senden.
            </div>
        <?php endif; ?>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="home.php">
                        <div class="form-group">
                            <label for="email">E-Mail-Adresse:</label>
                            <input type="email" class="form-control" id="email" name="email" onkeyup="checkemail()" required>
                            <span id="errorDisplaymail"></span>
                        </div>
                        <div class="form-group">
                            <label for="password">Passwort:</label>
                            <input type="password" class="form-control" id="password" name="pw" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="submit">Anmelden</button>
                        <?php
                            if (isset($error_message)) {
                                echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                            }
                        ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>