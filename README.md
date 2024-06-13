# TGMEATS

### 1. Einführung

Wir haben uns entschieden, MariaDB als unsere bevorzugte Datenbanklösung zu nutzen. Diese Wahl basiert auf unserer umfangreichen Erfahrung mit diesem System, die es uns ermöglicht, effizient und effektiv damit zu arbeiten. Darüber hinaus haben wir MariaDB bereits auf all unseren Laptops eingerichtet und konfiguriert, was uns erheblich Zeit und Mühe erspart. Durch diese vorausgehenden Maßnahmen können wir sofort mit unserer Arbeit beginnen, ohne zusätzlichen Aufwand für die Einrichtung und Einarbeitung aufwenden zu müssen. Diese Entscheidung erleichtert uns den Workflow und trägt zur Optimierung unserer Prozesse bei.

### 2. Projektbeschreibung

Unser Projektteam, bestehend aus vier Schülern des TGM, entwickelt eine Website, die allen Schülern der Schule dabei hilft, die besten Restaurants zu finden. Die Website bietet verschiedene Kategorien und Filtermöglichkeiten, um den individuellen Vorlieben gerecht zu werden. Basierend auf diesen Kriterien werden passende Restaurantvorschläge angezeigt. Benutzer können die vorgeschlagenen Restaurants bewerten, durch ein Sterne-System.

### 3. Theorie

Das vorgestellte Projekt soll eine Website werden. Für das Frontend haben wir HTML unter anderem mit Bootstrap Code geschrieben und für das Backend mit PHP und einer mariaDB Datenbank gearbeitet wo die Support Anfragen, User Daten, Sterne etc. gespeichert sind. Es wurde ein lokales usbserver verwendet für dieses Projekt. Wir haben eine Tabelle mit Restaurants in der Nähe angefertigt die wir dann in unsere Datenbank manuell eingetragen haben.

1. **Datenbank mit mariaDB und Adminer**

    **MariaDB:** MariaDB ist ein Open-Source, relationales Datenbankmanagementsystem,     das als Fork von MySQL entwickelt wurde. Es zeichnet sich durch hohe Kompatibilität     mit MySQL, ausgezeichnete Leistung, erweiterte Sicherheitsfunktionen und gute     Skalierbarkeit aus. Unterstützt von einer aktiven Community und regelmäßigen         Updates, ist MariaDB sowohl für kleine Projekte als auch für große     Unternehmensanwendungen geeignet.

    **Adminer:** Adminer ist ein leistungsfähiges und leichtgewichtiges     Datenbankverwaltungstool. Es unterstützt mehrere Datenbankmanagementsysteme     wie MySQL, MariaDB, PostgreSQL, SQLite und MSSQL.

### 4. Arbeitsschritte

    1. **Umgebungen aufsetzen**

        Da wir schon alle Anwendungen wie PHP, HTML und mariaDB aufgesetzt haben         konnten wir diesen Schritt überspringen

    2. **FrontEnd**

        Das einzige Problem beim Frontend war nur dass wir den Footer nicht dynamisch         hingekriegt haben weshalb wir es ausgelassen haben sonst ging alles fix

    3. **BackEnd**

        3.1 **Database aufsetzen**

              In DataGrip haben wir die Datenbank "TGMEATS" aufgesetzt

        3.2 **User zum zugreifen auf die Datenbank festlegen**

            ``CREATE USER "Edan"@"%" IDENTIFIED BY "erendaniel";``
            ``GRANT ALL PRIVILEGES ON TGMEATS.* TO "Edan"@"%";``

            um mit PHP darauf zuzugreifen muss man die so eingeben:

             ``$servername = "localhost";``

            ``$username = "Edan";``

            ``$password = "erendaniel";``

            ``$dbname = "TGMEATS";``

            ``$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username,``            ``$password); ``

        3.3 **Anmelden und Registrieren**

            Wenn man sich registriert sollen die Daten in die Datenbank gespeichert werden             und zwar die email als Primary Key damit man nicht sich 2 mal mit der gleichen             Mail registriert und das Passwort verschlüsselt und zwar mit der Methode:             ``$hash = password_hash($passwort, PASSWORD_BCRYPT);`` damit man das             Passwort nur verschlüsselt speichert und beim Anmelden gibt es die             Umwandlungsmethode: ``password_verify($password, $row["PASSWORT"])``

### 5. Zusammenfassung

Unser Problem in diesem Projekt war dass wir eigentlich eine API benutzen wollten aber da dies zu umständlich war haben wir einfach eine Tabelle mit den besten uns bekannten Restaurants in der Nähe erstellt und dies dann manuell in die Datenbank reingeschrieben. Gegen Ende hatten wir Zeitprobleme aber sonst ging alles fast wie geplant.
