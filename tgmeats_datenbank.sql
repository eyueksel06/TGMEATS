DROP DATABASE IF EXISTS TGMEATS;
CREATE DATABASE IF NOT EXISTS TGMEATS;
USE TGMEATS;

CREATE TABLE Restaurants(
  id INTEGER(50) PRIMARY KEY,
  name VARCHAR(50),
  kategorie VARCHAR(50),
  maps VARCHAR(500),
  bildurl VARCHAR(500) NULL
);


CREATE TABLE Benutzerdaten(
    EMAIL VARCHAR(100) PRIMARY KEY,
    PASSWORT VARCHAR (999)
);
CREATE TABLE SterneBewertungen (
    BEWERTUNGS_ID INT AUTO_INCREMENT PRIMARY KEY,
    RESTAURANT_ID INT NOT NULL,
    STERNE INT NOT NULL,
    USER_BEWERTUNG VARCHAR(255),
    FOREIGN KEY (USER_BEWERTUNG) REFERENCES Benutzerdaten(EMAIL),
    FOREIGN KEY (RESTAURANT_ID) REFERENCES Restaurants(id)
);

CREATE TABLE Support(
    EMAIL VARCHAR(50),
    Titel VARCHAR(100),
    supporttext VARCHAR(550),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (EMAIL) REFERENCES Benutzerdaten(EMAIL)
);

INSERT INTO Benutzerdaten (EMAIL, PASSWORT) VALUES
('dwojtasik@student.tgm.ac.at', '$2y$10$r8R5FCATUm2XYClpIWBeJOFOwK082t5VXSWoHVaxoU0DS5YqmibuW');

INSERT INTO Restaurants VALUES
(1, 'Noodle King', 'Asiatisch', 'https://maps.app.goo.gl/9hxZvHrv18ioSTZo7', NULL),
(2, 'Kent', 'Türkisch', 'https://maps.app.goo.gl/P6g137AE7Z1pPcva9', 'images/kent-restaurant.jpg'),
(3, 'Tutto Frutti', 'Dessert', 'https://maps.app.goo.gl/cMEs7jK7z2aLEY7g8', NULL),
(4, 'Kurze Pause', 'Türkisch', 'https://maps.app.goo.gl/FC2d2kabbz38drfB6', NULL),
(5, 'Isos Döner', 'Türkisch', 'https://maps.app.goo.gl/1xRbZDMXYF8W3pHLA', NULL),
(6, 'Pariser Döner', 'Türkisch', 'https://maps.app.goo.gl/ukxVmfvimKTDsWHPA', 'images/Pariser_Döner.png'),
(7, 'Döner Bar', 'Türkisch', 'https://maps.app.goo.gl/tjQipBCrJgBmj4vv9', 'images/Döner-Bar.jpg'),
(8, 'Hühner Paradies', 'Hähnchen', 'https://maps.app.goo.gl/WhRFAJTdjEoztKyS8', NULL),
(9, 'Dominos', 'Pizzeria', 'https://maps.app.goo.gl/4BCvv7QfKYDL4uy87', 'images/Dominos.png'),
(10, 'Leonardelli', 'Dessert', 'https://maps.app.goo.gl/Ea5quaAPMsaK4oWF9', NULL),
(11, 'Diwan', 'Türkisch', 'https://maps.app.goo.gl/JsaVtwxryggBc3an8', NULL),
(12, 'Gandum Tandori', 'Persisch', 'https://maps.app.goo.gl/zZisyqETdbedXkb27', NULL),
(13, 'Burger Brothers', 'Fast Food', 'https://maps.app.goo.gl/GoQo9Uj7cQs6bBss5', NULL),
(14, 'McDonalds Handelskai', 'Fast Food', 'https://maps.app.goo.gl/6hqov8qGuiY6J5TJ7', NULL),
(15, 'McDonalds Heiligenstadt', 'Fast Food', 'https://maps.app.goo.gl/YyEQSED8DQ6hT2FHA', NULL),
(16, 'Bistro Europa', 'Türkisch', 'https://maps.app.goo.gl/v9pg7vAeUNzQCdvE9', NULL),
(17, 'Chutney Indian Food', 'Indisch', 'https://maps.app.goo.gl/ZdAquSDMrQ3hqYww7', NULL),
(18, 'D''Lounge', 'Italienisch', 'https://maps.app.goo.gl/5WmeSsSi5eoDfBtd9', NULL),
(19, 'Pandos / Tex Mex', 'Mexikanisch', 'https://maps.app.goo.gl/oYeHKzMSAinuFPpW8', NULL),
(20, 'KFC Millenium', 'Fast Food', 'https://maps.app.goo.gl/4uPUqRNMBtKqGfYt6', NULL),
(21, 'Burger King Millenium', 'Fast Food', 'https://maps.app.goo.gl/dWG4gyKbrjFqZfvp8', 'images/burgerking.jpg'),
(22, 'Kaiser''s – Kaiser''schmarrn', 'Dessert', 'https://maps.app.goo.gl/65L8GwknPxetk7pv6', NULL),
(23, 'Pani Bär', 'Mongolisch', 'https://maps.app.goo.gl/eChDFaPUGVZzQpM26', NULL),
(24, 'Bayram Pizza', 'Pizzeria', 'https://maps.app.goo.gl/7yHv67wMcHwtt5qv8', NULL),
(25, 'China Restaurant', 'Asiatisch', 'https://maps.app.goo.gl/DVtJDZAdtpYfKhoo6', NULL),
(26, 'Bosna Grill OG', 'Osteuropäisch', 'https://maps.app.goo.gl/5hdwbYhv1X44vPK98', NULL),
(27, 'Abu Elabed Restaurant', 'Arabisch', 'https://maps.app.goo.gl/Lv7ZQftMYFkTopTG6', NULL),
(28, 'Çiğköftem', 'Türkisch', 'https://maps.app.goo.gl/aCBMd7osWXFTgbrj6', NULL),
(29, 'Yummy', 'Asiatisch', 'https://maps.app.goo.gl/qWUcXpnvbTyFBwtp8', NULL),
(30, 'Jacks Burger', 'Burger', 'https://maps.app.goo.gl/Z77rvna1B2wP8mESA', NULL),
(31, 'Vevi Restaurant', 'Vegan', 'https://maps.app.goo.gl/nn5yf6m8notaamcy6', NULL),
(32, 'Noodle House', 'Asiatisch', 'https://maps.app.goo.gl/ackDUfwW7EeZuY967', NULL),
(33, 'Bäckquem', 'Bäckerei', 'https://maps.app.goo.gl/CBPn7qXsRhw7YT8L7', NULL),
(34, 'Ströck', 'Bäckerei', 'https://maps.app.goo.gl/mXzV3uTMXCn2bj5L8', NULL),
(35, 'Bäckerei Gül', 'Bäckerei', 'https://maps.app.goo.gl/zXAW1yt6LYuPoFKY8', NULL);

-- Beispiel-Daten in Support-Tabelle einfügen
INSERT INTO Support (EMAIL, Titel, supporttext) VALUES
('dwojtasik@student.tgm.ac.at', 'Probleme mit dem Login', 'Ich habe ein Problem mit meinem Account.');

INSERT INTO SterneBewertungen(BEWERTUNGS_ID, RESTAURANT_ID, STERNE, USER_BEWERTUNG) VALUES
(1,6,3, 'dwojtasik@student.tgm.ac.at');


#CREATE USER "Edan"@"%" IDENTIFIED BY "erendaniel";
GRANT ALL PRIVILEGES ON TGMEATS.* TO "Edan"@"%";
FLUSH PRIVILEGES;

-- Bestehende Tabelle ändern, um created_at Spalte hinzuzufügen
SELECT * FROM Restaurants;
SELECT * FROM Benutzerdaten;
SELECT * FROM Support;
SELECT * FROM SterneBewertungen;

