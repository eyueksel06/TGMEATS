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

    // Abrufen der ID des Restaurants 'Kent'
    $stmtv = $conn->prepare("SELECT id FROM Restaurants WHERE name = :name");
    $name = "Kent";
    $stmtv->bindParam(":name", $name);
    $stmtv->execute();
    $restaurant = $stmtv->fetch(PDO::FETCH_ASSOC);
    $restaurant_id = $restaurant ? $restaurant['id'] : null;

    if (isset($_POST["submit"])) {
        if ($is_logged_in) {
            $error_message = "Sie sind bereits angemeldet.";
        } else {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = filter_input(INPUT_POST, 'pw', FILTER_SANITIZE_STRING);
            
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
                    header("Location: foodspots.php"); // Weiterleitung zur about_us.php Seite
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
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $passwort = filter_input(INPUT_POST, 'passwort', FILTER_SANITIZE_STRING);

            try {
                if (!empty($email) && !empty($passwort)) {
                    // Überprüfen, ob die E-Mail bereits in der Datenbank vorhanden ist
                    $checkEmailQuery = "SELECT * FROM Benutzerdaten WHERE EMAIL = :email";
                    $stmt = $conn->prepare($checkEmailQuery);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        // Wenn die E-Mail bereits vorhanden ist, geben Sie eine entsprechende Fehlermeldung aus
                        $emailexistiert = "Die eingegebene E-Mail-Adresse existiert bereits.";
                    } else {
                        // Wenn die E-Mail nicht vorhanden ist, fügen Sie den Benutzer zur Datenbank hinzu
                        $stmt = $conn->prepare("INSERT INTO Benutzerdaten (email, passwort) VALUES (:user, :hashpw)");
                        $stmt->bindParam(":user", $email);
                        $hash = password_hash($passwort, PASSWORD_BCRYPT);
                        $stmt->bindParam(":hashpw", $hash);
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
        header("Location: home.php");
        exit();
    }

    if ($is_logged_in && isset($_POST["stars"]) && $_POST["stars"] > 0 && isset($_POST["senden"])) {
      $stmtv = $conn->prepare("SELECT id FROM Restaurants WHERE name = :name");
      $name = $_POST["restaurant_id"];
      $stmtv->bindParam(":name", $name);
      $stmtv->execute();
  
      $stars = intval($_POST["stars"]);
      $restaurant = $stmtv->fetch(PDO::FETCH_ASSOC);
      $restaurant_id = $restaurant ? $restaurant['id'] : null;
  
      // Definiere die E-Mail-Adresse des Benutzers außerhalb der Bedingung
      $user_bewertung = $_SESSION["email"];
  
      // Überprüfe, ob der Benutzer bereits eine Bewertung für dieses Restaurant abgegeben hat
      $stmt_check = $conn->prepare("SELECT * FROM SterneBewertungen WHERE RESTAURANT_ID = :restaurant_id AND USER_BEWERTUNG = :user_bewertung");
      $stmt_check->bindParam(":restaurant_id", $restaurant_id, PDO::PARAM_INT);
      $stmt_check->bindParam(":user_bewertung", $user_bewertung, PDO::PARAM_STR);
      $stmt_check->execute();
      $existing_rating = $stmt_check->fetch(PDO::FETCH_ASSOC);
  
      if ($existing_rating) {
          // Wenn eine Bewertung gefunden wurde, aktualisiere sie
          $stmt_update = $conn->prepare("UPDATE SterneBewertungen SET STERNE = :stars WHERE RESTAURANT_ID = :restaurant_id AND USER_BEWERTUNG = :user_bewertung");
          $stmt_update->bindParam(":stars", $stars, PDO::PARAM_INT);
          $stmt_update->bindParam(":restaurant_id", $restaurant_id, PDO::PARAM_INT);
          $stmt_update->bindParam(":user_bewertung", $user_bewertung, PDO::PARAM_STR);
          $stmt_update->execute();
      } else {
          // Wenn keine Bewertung gefunden wurde, füge eine neue Bewertung hinzu
          $stmt_insert = $conn->prepare("INSERT INTO SterneBewertungen (RESTAURANT_ID, STERNE, USER_BEWERTUNG) VALUES (:restaurant_id, :stars, :user_bewertung)");
          $stmt_insert->bindParam(":restaurant_id", $restaurant_id, PDO::PARAM_INT);
          $stmt_insert->bindParam(":stars", $stars, PDO::PARAM_INT);
          $stmt_insert->bindParam(":user_bewertung", $user_bewertung, PDO::PARAM_STR);
          $stmt_insert->execute();
      }
  }
  
  
  
  
  
  
  
} catch(PDOException $e) {
    $error_message = "Verbindung fehlgeschlagen: " . $e->getMessage();
}
?>



<!DOCTYPE html>
<html lang="en">
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

    function setStars(element, stars) {
        var card = element.closest('.card');
        card.querySelector('.stars').value = stars;
    }

  </script>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link href="foodspots_cards.css?v=1.0" rel="stylesheet">
  <script src="foodspots.js"></script>
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
        <li class="nav-item active">
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
           <a class="nav-link active" href="foodspots.php">Foodspot</a>
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
          <button type="submit" class="btn btn-primary" name="submit" onclick="checkemail()">Anmelden</button>
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
        <form method="post" id="registrierungsForm">
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
        <h1 class="display-4">Foodspots</h1>
        <p class="lead">Zeit für die Suche</p>
    </div>
</header>

<div class="container ">
    <divc class="title-container"><h1>Döner</h1></div>
      <br>  <!-- Überschrift -->
    <div class="row">
       
        <div class="col my-5">
            <div class="card">
                <div class="card-picture" style="background-image: url('images/kent-restaurant.jpg'); background-size: cover; background-position: center; height: 300px;"> <!-- Bild -->
                </div>
                <div class="card-title">
                    <h3>Kent</h3> <!-- Name -->
                </div>
                <div class="row">
                    <div class="col-auto">
                        <div class="star-rating" data-restaurant="Kent">
                            <i class="fa fa-star" onclick="setStars(this, 1)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 2)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 3)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 4)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 5)"></i>
                        </div>
                    </div>
                    <div class="col-auto d-flex justify-content-end">
                        <div class="card-buttons">
                            <form method="post" action="foodspots.php">
                                <input type="hidden" class="stars" name="stars" value="">
                                <input type="hidden" class="restaurant_id" name="restaurant_id" value="Kent">
                                <button type="submit" name="senden" class="btn btn-warning me-2">Senden</button>
                                <?php
                                if (isset($error_message)) {
                                    echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                                }
                                if (isset($success_message)) {
                                    echo '<div class="alert alert-success mt-3">' . $success_message . '</div>';
                                }
                                ?>
                            </form>
                            <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/P6g137AE7Z1pPcva9'">Route</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <!-- Zweite Karte -->
        <div class="col my-5">
            <div class="card">
                <div class="card-picture" style="background-image: url('images/Pariser_Döner.png'); background-size: cover; background-position: center; height: 300px;">
                </div>
                <div class="card-title">
                    <h3>Pariser Döner</h3>
                </div>
                <div class="row">
                    <div class="col-auto">
                    <div class="star-rating" data-restaurant="Pariser Döner">
                            <i class="fa fa-star" onclick="setStars(this, 1)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 2)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 3)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 4)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 5)"></i>
                        </div>
                    </div>
                    <div class="col-auto d-flex justify-content-end">
                        <div class="card-buttons">
                        <form method="post" action="foodspots.php">
                        <button type="submit" name="senden" class="btn btn-warning me-2">Senden</button>
                                <input type="hidden" class="stars" name="stars" value="">
                                <input type="hidden" class="restaurant_id" name="restaurant_id" value="Pariser Döner">
                                
                                <?php
                                if (isset($error_message)) {
                                    echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                                }
                                if (isset($success_message)) {
                                    echo '<div class="alert alert-success mt-3">' . $success_message . '</div>';
                                }
                                ?>
                            </form>
                            <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/ukxVmfvimKTDsWHPA'">Route</button> <!-- Route -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dritte Karte -->
        <div class="col my-5">
            <div class="card">
                <div class="card-picture" style="background-image: url('images/Döner-Bar.jpg'); background-size: cover; background-position: center; height: 300px;">
                </div>
                <div class="card-title">
                    <h3>Döner Bar</h3>
                </div>

                <div class="row">
                    <div class="col-auto">
                        <div class="star-rating" data-restaurant="Döner Bar">
                            <i class="fa fa-star" onclick="setStars(this, 1)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 2)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 3)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 4)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 5)"></i>
                        </div>
                    </div>
                    <div class="col-auto d-flex justify-content-end">
                        <div class="card-buttons">
                            <form method="post" action="foodspots.php">
                                <input type="hidden" class="stars" name="stars" value="">
                                <input type="hidden" class="restaurant_id" name="restaurant_id" value="Döner Bar">
                                <button type="submit" name="senden" class="btn btn-warning me-2">Senden</button>
                                <?php
                                if (isset($error_message)) {
                                    echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                                }
                                if (isset($success_message)) {
                                    echo '<div class="alert alert-success mt-3">' . $success_message . '</div>';
                                }
                                ?>
                            </form>
                            <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/P6g137AE7Z1pPcva9'">Route</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        
        <!-- Vierte Karte -->
        <div class="col my-5">
          <div class="card">
              <div class="card-picture" style="background-image: url('images/kurzepause.jpg'); background-size: cover; background-position: center; height: 300px;">
              </div>
              <div class="card-title">
                  <h3>Kurze Pause</h3>
              </div>
              <div class="row">
                    <div class="col-auto">
                        <div class="star-rating" data-restaurant="Kurze Pause">
                            <i class="fa fa-star" onclick="setStars(this, 1)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 2)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 3)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 4)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 5)"></i>
                        </div>
                    </div>
                    <div class="col-auto d-flex justify-content-end">
                        <div class="card-buttons">
                            <form method="post" action="foodspots.php">
                                <input type="hidden" class="stars" name="stars" value="">
                                <input type="hidden" class="restaurant_id" name="restaurant_id" value="Kurze Pause">
                                <button type="submit" name="senden" class="btn btn-warning me-2">Senden</button>
                                <?php
                                if (isset($error_message)) {
                                    echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                                }
                                if (isset($success_message)) {
                                    echo '<div class="alert alert-success mt-3">' . $success_message . '</div>';
                                }
                                ?>
                            </form>
                            <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/P6g137AE7Z1pPcva9'">Route</button>
                        </div>
                    </div>
              </div>
          </div>
      </div>

      <div class="col my-5">
        <div class="card">
            <div class="card-picture" style="background-image: url('images/isos.jpg'); background-size: cover; background-position: center; height: 300px;">
            </div>
            <div class="card-title">
                <h3>Isos Döner</h3>
            </div>
            <div class="row">
                    <div class="col-auto">
                        <div class="star-rating" data-restaurant="Isos Döner">
                            <i class="fa fa-star" onclick="setStars(this, 1)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 2)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 3)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 4)"></i>
                            <i class="fa fa-star" onclick="setStars(this, 5)"></i>
                        </div>
                    </div>
                    <div class="col-auto d-flex justify-content-end">
                        <div class="card-buttons">
                            <form method="post" action="foodspots.php">
                                <input type="hidden" class="stars" name="stars" value="">
                                <input type="hidden" class="restaurant_id" name="restaurant_id" value="Isos Döner">
                                <button type="submit" name="senden" class="btn btn-warning me-2">Senden</button>
                                <?php
                                if (isset($error_message)) {
                                    echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                                }
                                if (isset($success_message)) {
                                    echo '<div class="alert alert-success mt-3">' . $success_message . '</div>';
                                }
                                ?>
                            </form>
                            <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/P6g137AE7Z1pPcva9'">Route</button>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <div class="col my-5">
      <div class="card">
          <div class="card-picture" style="background-image: url('images/diwan.png'); background-size: cover; background-position: center; height: 300px;">
          </div>
          <div class="card-title">
              <h3>Diwan</h3>
          </div>
          <div class="row">
              <div class="col-auto">
                <div class="star-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
              </div>
              </div>
              <div class="col-auto d-flex justify-content-end">
                  <div class="card-buttons">
                      <button class="btn btn-warning me-2">Senden</button>
                      <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/JsaVtwxryggBc3an8'">Route</button> <!-- Route -->
                  </div>
              </div>
          </div>
      </div>
  </div>

  





     
    </div>

    <div class="container ">
      <divc class="title-container"><h1>Pizza</h1></div>
        <br>  <!-- Überschrift -->
      <div class="row">
        <div class="col my-5">
          <div class="card">
              <div class="card-picture" style="background-image: url('images/Dominos.png'); background-size: cover; background-position: center; height: 300px;">
              </div>
              <div class="card-title">
                  <h3>Dominos</h3>
              </div>
              <div class="row">
                  <div class="col-auto">
                    <div class="star-rating">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                  </div>
                  </div>
                  <div class="col-auto d-flex justify-content-end">
                      <div class="card-buttons">
                          <button class="btn btn-warning me-2">Senden</button>
                          <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/4BCvv7QfKYDL4uy87'">Route</button> <!-- Route -->
                      </div>
                  </div>
              </div>
          </div>
      </div>
      

      <div class="col my-5">
        <div class="card">
            <div class="card-picture" style="background-image: url('images/dlounge.jpg'); background-size: cover; background-position: center; height: 300px;">
            </div>
            <div class="card-title">
                <h3>D'Lounge</h3>
            </div>
            <div class="row">
                <div class="col-auto">
                  <div class="star-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                </div>
                <div class="col-auto d-flex justify-content-end">
                    <div class="card-buttons">
                        <button class="btn btn-warning me-2">Senden</button>
                        <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/5WmeSsSi5eoDfBtd9'">Route</button> <!-- Route -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col my-5">
      <div class="card">
          <div class="card-picture" style="background-image: url('images/platzhalter.png'); background-size: cover; background-position: center; height: 300px;">
          </div>
          <div class="card-title">
              <h3>Bayram Pizza</h3>
          </div>
          <div class="row">
              <div class="col-auto">
                <div class="star-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
              </div>
              </div>
              <div class="col-auto d-flex justify-content-end">
                  <div class="card-buttons">
                      <button class="btn btn-warning me-2">Senden</button>
                      <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/7yHv67wMcHwtt5qv8'">Route</button> <!-- Route -->
                  </div>
              </div>
          </div>
      </div>
  </div>

    

    
    
      


</div>
<br><br>
<div class="container ">
  <divc class="title-container"><h1>Fast Food</h1></div>
    <br>  <!-- Überschrift -->
  <div class="row">

    <div class="col my-5">
      <div class="card">
          <div class="card-picture" style="background-image: url('images/burgerking.jpg'); background-size: cover; background-position: center; height: 300px;">
          </div>
          <div class="card-title">
              <h3>Burger King</h3>
          </div>
          <div class="row">
              <div class="col-auto">
                <div class="star-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
              </div>
              </div>
              <div class="col-auto d-flex justify-content-end">
                  <div class="card-buttons">
                      <button class="btn btn-warning me-2">Senden</button>
                      <button class="btn btn-success" onclick="window.location.href='https://maps.app.goo.gl/dWG4gyKbrjFqZfvp8'">Route</button> <!-- Route -->
                  </div>
              </div>
          </div>
      </div>
  </div>

      


<!-- Bootstrap JS, Popper.js, and jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
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

