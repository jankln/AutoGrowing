<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pflanzenverwaltung</title>
</head>
<body>

<h2>Pflanzenverwaltung</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Wachstumstemperatur (°C)</th>
        <th>Lichtdauer (Minuten/Tag)</th>
        <th>Bodenfeuchtigkeit beim Wachsen</th>
        <th>Luftfeuchtigkeit beim Wachsen (%)</th>
        <th>Wasserbedarf pro Tag (Sekunden)</th>
        <th>Reagiert aktiv auf Bodenfeuchtigkeit?</th>
    </tr>
    <?php
     $databaseFile = 'AutoGrowing.db';

     try {
         // Verbindung zur SQLite-Datenbank herstellen
         $db = new SQLite3($databaseFile);

        
     } catch (Exception $e) {
         // Fehler beim Verbindungsaufbau
         echo "Fehler beim Verbindungsaufbau zur SQLite-Datenbank: " . $e->getMessage();
     }
    // Abfrage zum Abrufen der Pflanzendaten
    $stmt = $db->query('SELECT * FROM Plant');
    
    // Datensätze anzeigen
    while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {        echo "<tr>";
        echo "<td>".$row['plantID']."</td>";
        echo "<td>".$row['commonName']."</td>";
        echo "<td>".$row['growingTemperature']."</td>";
        echo "<td>".$row['lightDurationInMinutesPerDay']."</td>";
        echo "<td>".$row['growingSoilMoisture']."</td>";
        echo "<td>".$row['growingAirHumidity']."</td>";
        echo "<td>".$row['waterRequirementPerDay']."</td>";
        echo "<td>".($row['actOnSoilMoisture'] ? 'Ja' : 'Nein')."</td>";
        echo "</tr>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['commonName'], $_POST['growingTemperature'], $_POST['lightDuration'], $_POST['growingSoilMoisture'], $_POST['growingAirHumidity'], $_POST['waterRequirement'], $_POST['actOnSoilMoisture'], $_POST['Plantinsert_button'])) {        
        $commonName = $_POST['commonName'];
        $growingTemperature = $_POST['growingTemperature'];
        $lightDuration = $_POST['lightDuration'];
        $growingSoilMoisture = $_POST['growingSoilMoisture'];
        $growingAirHumidity = $_POST['growingAirHumidity'];
        $waterRequirement = $_POST['waterRequirement'];
        $actOnSoilMoisture = $_POST['actOnSoilMoisture'];
    
        // SQL-Statement zum Einfügen der Daten in die Datenbank vorbereiten
        $stmt = $db->prepare('INSERT INTO Plant (commonName, growingTemperature, lightDurationInMinutesPerDay, growingSoilMoisture, growingAirHumidity, waterRequirementPerDay, actOnSoilMoisture) VALUES (:commonName, :growingTemperature, :lightDuration, :growingSoilMoisture, :growingAirHumidity, :waterRequirement, :actOnSoilMoisture)');
    
        // Parameter binden
        $stmt->bindValue(':commonName', $commonName, SQLITE3_TEXT);
        $stmt->bindValue(':growingTemperature', $growingTemperature, SQLITE3_INTEGER);
        $stmt->bindValue(':lightDuration', $lightDuration, SQLITE3_INTEGER);
        $stmt->bindValue(':growingSoilMoisture', $growingSoilMoisture, SQLITE3_INTEGER);
        $stmt->bindValue(':growingAirHumidity', $growingAirHumidity, SQLITE3_INTEGER);
        $stmt->bindValue(':waterRequirement', $waterRequirement, SQLITE3_INTEGER);
        $stmt->bindValue(':actOnSoilMoisture', $actOnSoilMoisture, SQLITE3_INTEGER);
    
        // SQL-Statement ausführen
        $result = $stmt->execute();
    
        // Überprüfen, ob das Einfügen erfolgreich war
        if ($result) {
            echo "Neue Pflanze wurde erfolgreich zur Datenbank hinzugefügt.";
            header("Refresh:0");
            exit();
        } else {
            echo "Fehler beim Einfügen der neuen Pflanze in die Datenbank.";
        }
    }

    ?>
</table>

<h3>Neue Pflanze hinzufügen</h3>
<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
<label for="commonName">Name:</label>
    <input type="text" name="commonName" required><br>
    <label for="growingTemperature">Wachstumstemperatur (°C):</label>
    <input type="number" name="growingTemperature" required><br>
    <label for="lightDuration">Lichtdauer (Minuten/Tag):</label>
    <input type="number" name="lightDuration" required><br>
    <label for="growingSoilMoisture">Bodenfeuchtigkeit beim Wachsen:</label>
    <input type="number" name="growingSoilMoisture" required><br>
    <label for="growingAirHumidity">Luftfeuchtigkeit beim Wachsen (%):</label>
    <input type="number" name="growingAirHumidity" required><br>
    <label for="waterRequirement">Wasserbedarf pro Tag (Sekunden):</label>
    <input type="number" name="waterRequirement" required><br>
    <label for="actOnSoilMoisture">Reagiert aktiv auf Bodenfeuchtigkeit?</label>
    <select name="actOnSoilMoisture" required>
        <option value="1">Ja</option>
        <option value="0">Nein</option>
    </select><br>
    <input type="submit" value="Hinzufügen" name="Plantinsert_button">
</form>
<button id="GoBack" name="GoBack" onclick="weiterleitenStartseite()">Zurück zur Startseite</button>

</body>
</html>
