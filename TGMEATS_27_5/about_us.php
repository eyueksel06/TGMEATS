<?php 
session_start(); // Start der Sitzung

$servername = "localhost";
$username = "Edan";
$password = "erendaniel";
$dbname = "TGMEATS";

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
                  header("Location: about_us.php"); // Weiterleitung zur about_us.php Seite
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
            $checkEmailQuery = "SELECT * FROM Benutzerdaten WHERE EMAIL = '$email'";
            $stmt = $conn->prepare($checkEmailQuery);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
            if ($result) {
              // Wenn die E-Mail bereits vorhanden ist, geben Sie eine entsprechende Fehlermeldung aus
              $emailexistiert = "Die eingegebene E-Mail-Adresse existiert bereits.";
            } else {
              // Wenn die E-Mail nicht vorhanden ist, fügen Sie den Benutzer zur Datenbank hinzu
              $stmt = $conn->prepare ("INSERT INTO Benutzerdaten (email, passwort) VALUES (:user, :hashpw)");
              $stmt ->bindParam(":user",$email);
              $hash = password_hash($passwort, PASSWORD_BCRYPT);
              $stmt->bindParam(":hashpw",$hash);
              $stmt->execute();
        
            }
          } else {
            $fehler = "Bitte geben Sie eine gültige E-Mail-Adresse und ein Passwort ein.";
          }
        } catch(PDOException $e) {
            echo "Fehler: " . $e->getMessage();
        }
      }
    }
    if (isset($_POST["abmelden"])) {
        $_SESSION = array();
        session_destroy();
        header("Location: about_us.php");
        exit();
    }
} catch(PDOException $e) {
    $error_message = "Verbindung fehlgeschlagen: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TGM EATS</title>
  
  <script>
    function checkemail() {
      var email = document.getElementById("email").value;
      var errorDisplay4 = document.getElementById("errorDisplaymail");
      var correctemail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

      if(email.match(correctemail)) {
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
  <link href="styles.css" rel="stylesheet">
  
  <link href="kontakte.css?v=<?php echo time(); ?>" rel="stylesheet">

    <script src="about_us.js"></script>
  
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
          <li class="nav-item ">
           <a class="nav-link" href="home.php">Home</a>
        </li>
        <li class="nav-item">
           <a class="nav-link" href="foodspots.php">Foodspot</a>
        </li>
        <li class="nav-item">
           <a class="nav-link" href="support.php">Support</a>
        </li>
        <?php else: ?>
          <li class="nav-item ">
           <a class="nav-link" href="home.php">Home</a>
        </li>
        <li class="nav-item">
           <a class="nav-link" href="foodspots.php">Foodspot</a>
        </li>
        <?php endif; ?>
        <li class="nav-item active">
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
            <a href="#" id="loginIcon" class="nav-link" data-toggle="modal" data-target="#anmeldeModal">
              <i class="fas fa-user"></i>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
  
<!-- Anmelde-Modal -->
<div class="modal fade" id="anmeldeModal" tabindex="-1" role="dialog" aria-labelledby="anmeldeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="anmeldeModalLabel">Anmelden</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" action="">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email">
          </div>
          <div class="form-group">
            <label for="password">Passwort</label>
            <input type="password" class="form-control" id="password" name="pw" placeholder="Passwort">
          </div>
          <button type="submit" class="btn btn-primary" name="submit" onklick = "checkemail()" >Anmelden</button>
          <p id="errorDisplaymail">
            <?php if (isset($error_message)) echo $error_message; ?>
          </p>
          <p class="mt-2">Kein Konto? <a href="#" data-toggle="modal" data-target="#registrierungsModal" data-dismiss="modal">Hier erstellen</a></p>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Registrierungs-Modal -->
<div class="modal fade" id="registrierungsModal" tabindex="-1" role="dialog" aria-labelledby="registrierungsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrierungsModalLabel">Registrieren</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method = "post" id="registrierungsForm">
          <div class="form-group">
            <label for="regEmail">Email</label>
            <input type="email" class="form-control" id="regEmail" name="email" placeholder="Email">
          </div>
          <div class="form-group">
            <label for="regPassword">Passwort</label>
            <input type="password" class="form-control" id="regPassword" name="passwort" placeholder="Passwort">
          </div>
          <div class="form-group">
            <label for="regPassword-again">Passwort nochmal eingeben</label>
            <input type="password" class="form-control" id="regPassword-again" placeholder="Passwort nochmal">
            <small id="passwortMismatch" class="text-danger d-none">Passwörter stimmen nicht überein.</small>
            <script>
              document.getElementById("registrierungsForm").addEventListener("submit", function(event){
              var password = document.getElementById("regPassword").value;
              var confirmPassword = document.getElementById("regPassword-again").value;
    
              if (password !== confirmPassword) {
              event.preventDefault(); // Verhindert das Absenden des Formulars
              document.getElementById("passwortMismatch").classList.remove("d-none"); // Zeigt die Fehlermeldung an
              }
              });
            </script>
          </div>
          <button type="submit" name="registrieren" class="btn btn-primary">Registrieren</button>
        </form>
      </div>
    </div>
  </div>
</div>

  

    <!-- Header -->
    <header class="jumbotron jumbotron-fluid slim-header" style="background-color: #3a8d45; color: white;">
        <div class="container text-center">
            <h1 class="display-4">About Us</h1>
            <p class="lead">Meet the Team</p>
        </div>
    </header>

    <!-- Haupttext -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <p>Wir sind die Hauptverantwortlichen der TGMEATS Website. Wir sind TGM-Schüler, die sich entschieden haben, eine Website zu erstellen, die verschiedene Foodspots enthält, um das Leben der TGM-Schüler zu vereinfachen.</p>
            </div>
        </div>
    </div>

    <!-- Profilbilder -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-photo card-photo-1"></div> <!-- Verwenden Sie die entsprechende Klasse für das Bild -->
                    <div class="card-title">Furkan Güner <br>
                        <span>Manager / Frontend Developer</span>
                    </div>
                    <div class="card-socials">
                        <button class="card-socials-btn instagram" id="FurkanInsta" onclick="window.location.href='https://www.instagram.com/furkj47/'">
                            <img src="images/insta.png" alt="Instagram" width="24" height="24">
                        </button>
                        <button class="card-socials-btn instagram" id="FurkanInsta" onclick="window.location.href='https://www.tiktok.com/@furuhan1'">
                            <img src="images/tiktok.png" alt="TikTok" width="24" height="24">
                        </button>
                    </div>
                </div>
            </div>

            <!-- Weitere Profile hier einfügen -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-photo card-photo-4"></div>
                    <div class="card-title">Adem Shemsidini <br>
                        <span>Frontend Developer</span>
                    </div>
                    <div class="card-socials">
                        <button class="card-socials-btn instagram" id="AdemInsta" onclick="window.location.href='https://www.instagram.com/adem.shemsidini/'">
                            <img src="images/insta.png" alt="Instagram" width="24" height="24">
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-photo card-photo-2"></div>
                    <div class="card-title">Eren Yüksel <br>
                        <span>Backend Developer</span>
                    </div>
                    <div class="card-socials">
                        <button class="card-socials-btn instagram" id="ErenInsta" onclick="window.location.href='https://www.instagram.com/81_eren_ykl/'">
                            <img src="images/insta.png" alt="Instagram" width="24" height="24">
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-photo card-photo-3"></div>
                    <div class="card-title">Daniel Wojtasik <br>
                        <span>Backend Developer</span>
                    </div>
                    <div class="card-socials">
                        <button class="card-socials-btn instagram" id="DanielInsta" onclick="window.location.href='https://www.instagram.com/_dan1el23_7/'">
                            <img src="images/insta.png" alt="Instagram" width="24" height="24">
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js
    "></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Wenn das Anmelde-Symbol geklickt wird
            $('#loginIcon').click(function() {
                // Öffnen Sie das Anmelde-Modal
                $('#anmeldeModal').modal('show');
            });
        });
    </script>
</body>
</html>

