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
    <input type="submit" value="Hinzufügen">
</form>

</body>
</html>
