
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automatisiertes Gewächshaus</title>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div id="headline-container">
        <div id="headline">
            <h1>Automatisiertes Gewächshaus</h1>
        </div>
    </div>
    <div class="sidebar">
        <ul>
            <li><a href="#Steuerung">Steuerung</a></li>
            <li><a href="#plot">Alle Sensordaten</a></li>
            <li><a href="#plot2">Temperatur</a></li>
            <li><a href="#plot3">Luftfeuchtigkeit</a></li>
            <li><a href="#plot4">Bodenfeuchtigkeit</a></li>
        </ul>
        <hr class="sidebar-divider"> <!-- Horizontale Abtrennungslinie -->

        <div id="dropdown-container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <select id="plant-dropdown" name="plant-dropdown">
            <option disabled selected value> -- Wähle eine Pflanze -- </option>
                
                <?php
                $databaseFile = 'AutoGrowing.db';

                try {
                    // Verbindung zur SQLite-Datenbank herstellen
                    $db = new SQLite3($databaseFile);

                    // Erfolgreiche Verbindung
                    echo "Verbindung zur SQLite-Datenbank erfolgreich hergestellt.";
                } catch (Exception $e) {
                    // Fehler beim Verbindungsaufbau
                    echo "Fehler beim Verbindungsaufbau zur SQLite-Datenbank: " . $e->getMessage();
                }
               

                $result = $db->query('SELECT commonName FROM Plant');

                // Ergebnisse durchlaufen und Optionen für Dropdown-Menü erstellen
                while ($row = $result->fetchArray()) {
                    echo '<option value="' . $row['commonName'] . '">' . $row['commonName'] . '</option>';
                }


                    $sensorDataQuery = $db->query('SELECT dateTime, temperature, airHumidity, soilMoisture FROM SensorReadout');                   
                    $date_range = [];
                    $temperature = [];
                    $air_humidity = [];
                    $soilMoisture = [];
    
                    // Ergebnisse durchlaufen und Temperaturdaten in Arrays speichern
                    while ($row = $sensorDataQuery->fetchArray()) {
                        $date_range[] = $row['dateTime'];
                        $temperature[] = $row['temperature'];
                        $air_humidity[] = $row['airHumidity'];
                        $soilMoisture[] = $row['soilMoisture'];                    }

                        $selectedPlantQuery=$db->query("SELECT P.commonName from Plant P inner join PlantLog PL on P.plantID=PL.plantID order by PL.dateTime DESC limit 1");
                        $selectedPlant = $selectedPlantQuery->fetchArray()[0];
            ?>

            </select>
            <input type="submit" value="Auswählen" name="Plantsubmit_button">
            </form>
             
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                
            if (isset($_POST['reset-sensor-data'])) {
                exec("sqlite3 AutoGrowing.db < reset.sql");
            }

            if (isset($_POST['restart-pi'])) {
                exec("python3 restart.py");
            }
            
            if(isset($_POST['licht-button'])){
            }

            if(isset($_POST['wasser-button'])){
                exec("python3 Waterpump.py");
            }
            if(isset($_POST['servo-button'])){
                exec("python3 Servomotor.py");
            }

        

            if(isset($_POST['Plantsubmit_button'])){
                if(isset($_POST['plant-dropdown'])){
                    $selectedPlant=$_POST['plant-dropdown'];
                }
                //CurrentPlant ist aus datenbank, nicht gut
                //bei selectedPlant die ausgewählte Pflanze aus dem dropbown menu
                $currentPlantIDQuery=$db->query("SELECT plantID FROM Plant WHERE commonName='$selectedPlant'");
                $currentPlantID=$currentPlantIDQuery->fetchArray()[0];


                // Aktuelles Datum und Uhrzeit 
                $current_datetime = date('Y-m-d H:i:s');
                
                // Aktuelles Datum und Uhrzeit um eine Stunde erhöhen
                $current_datetime_plus_one_hour = date('Y-m-d H:i:s', strtotime($current_datetime . ' +1 hour'));

                $InsertPlantLog = "INSERT INTO PlantLog(dateTime, plantID) VALUES ('$current_datetime_plus_one_hour', '$currentPlantID')";
                $result = $db->exec($InsertPlantLog);
            }
           
            }


            $currentPlantTemperatureQuery = $db->query("SELECT growingTemperature FROM Plant WHERE commonName='$selectedPlant'");
            $currentPlantSoilHumidityQuery = $db->query("SELECT growingSoilMoisture FROM Plant WHERE commonName='$selectedPlant'");
 
            $currentPlantTemperature=$currentPlantTemperatureQuery->fetchArray()[0];
            $currentPlantSoilHumidity=$currentPlantSoilHumidityQuery->fetchArray()[0];
        

            ?>
            
        </div>
        <div id="plant-info">
            <p id="plant-name">Ausgewählte Pflanze: <?php echo $selectedPlant?></p>
            <p id="temperature">Benötigte Temperatur: <?php echo $currentPlantTemperature?>°C</p>
            <p id="humidity">Benötigte Bodenfeuchtigkeit: <?php echo $currentPlantSoilHumidity?>%</p>
            <p><a href="https://github.com/jankln/AutoGrowing.git">        
                <img src="GitHub.png" alt="GitHub" width="80" height="50"></a></p>
        </div>
    </div>
    <div class="section" id="Steuerung">
        <h3>Steuerung</h3>
        <br>
        <form method="post">
        <div class="button-container">
            <button id="licht-button" name="licht-button" type="submit">Licht an/aus</button>
            <button id="wasser-button" name="wasser-button" type="submit">Bewässerung an/aus</button>
            <button id="servo-button" name="servo-button" type="submit">Luftdurchzug öffnen</button>
            <button id="restart-button" name="restart-pi" type="submit">Raspberry Pi neustarten</button>
            <button type="submit" name="reset-sensor-data" id="reset-sensor-data">Sensordaten reset</button>
        </form>
        </div>
    </div>
   


<div id="plot" class="section"></div>
<script>
   var date_range = <?php echo json_encode($date_range); ?>;
    var air_humidity =<?php echo json_encode($air_humidity); ?>;
    var soil_humidity = <?php echo json_encode($soilMoisture); ?>;
    var temperature = <?php echo json_encode($temperature); ?>;
    var trace1 = {
        x: date_range,
        y: air_humidity,
        mode: 'lines',
        name: 'Luftfeuchtigkeit'
    };

    var trace2 = {
        x: date_range,
        y: soil_humidity,
        mode: 'lines',
        name: 'Bodenfeuchtigkeit'
    };

    

    var trace4 = {
        x: date_range,
        y: temperature,
        mode: 'lines',
        name: 'Temperatur'
    };

    var data = [trace1, trace2, trace4];

    var layout = {
        title: 'Sensordaten',
        xaxis: {title: 'Datum und Uhrzeit'},
        yaxis: {title: 'Wert'},
        xaxis_rangeslider_visible: true
    };

    Plotly.newPlot('plot', data, layout);
</script>

<div id="plot2" class="section"></div>
<script>

    var trace1 = {
        x: date_range,
        y: temperature,
        mode: 'lines',
        name: 'Temperatur in °C'
    };

    var data = [trace1];

    var layout = {
        title: 'Temperatur',
        xaxis: {title: 'Datum und Uhrzeit'},
        yaxis: {title: 'Temperatur in °C'},
        xaxis_rangeslider_visible: true
    };

    Plotly.newPlot('plot2', data, layout);
</script>

<div id="plot3" class="section"></div>
<script>

    var trace1 = {
        x: date_range,
        y: air_humidity,
        mode: 'lines',
        name: 'Luftfeuchtigkeit'
    };

    var data = [trace1];

    var layout = {
        title: 'Luftfeuchtigkeit',
        xaxis: {title: 'Datum und Uhrzeit'},
        yaxis: {title: 'Luftfeuchtigkeit in %'},
        xaxis_rangeslider_visible: true
    };

    Plotly.newPlot('plot3', data, layout);
</script>

<div id="plot4" class="section"></div>
<script>

    var trace1 = {
        x: date_range,
        y: soil_humidity,
        mode: 'lines',
        name: 'Bodenfeuchtigkeit'
    };

    var data = [trace1];

    var layout = {
        title: 'Bodenfeuchtigkeit',
        xaxis: {title: 'Datum und Uhrzeit'},
        yaxis: {title: 'Bodenfeuchtigkeit in %'},
        xaxis_rangeslider_visible: true
    };

    Plotly.newPlot('plot4', data, layout);
</script>




</body>
</html>
