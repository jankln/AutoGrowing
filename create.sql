drop TABLE if EXISTS SensorReadout;
drop table if exists PlantLog;
drop table if exists Plant;
CREATE TABLE IF NOT EXISTS SensorReadout (
        ID                              INTEGER PRIMARY KEY AUTOINCREMENT,
        dateTime                DATETIME NOT NULL,
        temperature             INTEGER NOT NULL,
        airHumidity             INTEGER NOT NULL,
        soilMoisture    INTEGER NOT NULL,
        doorIsOpen              BOOLEAN NOT NULL,
        waterIsOn               BOOLEAN NOT NULL,
        lightIsOn               BOOLEAN NOT NULL
);

CREATE TABLE IF NOT EXISTS Plant(
        plantID                                                 INTEGER PRIMARY KEY AUTOINCREMENT,
        commonName                                              VARCHAR(80) NOT NULL,
        growingTemperature                              INTEGER NOT NULL, -- in °C
        lightDurationInMinutesPerDay    INTEGER NOT NULL, -- in Minuten
        growingSoilMoisture                             INTEGER NOT NULL, -- als Wert zwischen 0 - maximal Sensor wert
        growingAirHumidity                              INTEGER NOT NULL, -- 1-100 %
        waterRequirementPerDay                  INTEGER NOT NULL, -- in Sekunden
        actOnSoilMoisture                               BOOLEAN NOT NULL  -- ist Erdfeuchtigkeit ein Faktor zum Gießen?
);

CREATE TABLE IF NOT EXISTS PlantLog(
        ID                              INTEGER PRIMARY KEY AUTOINCREMENT,
        plantID                 INTEGER NOT NULL,
        dateTime                DATETIME NOT NULL,
        FOREIGN KEY(plantID) REFERENCES Plant(ID)
);


INSERT INTO Plant (commonName, growingTemperature, lightDurationInMinutesPerDay, growingSoilMoisture, growingAirHumidity, waterRequirementPerDay, actOnSoilMoisture)
VALUES
('China Rose', 25, 600, 60, 200, 20, 0),
('Tomato', 25, 720, 70, 250, 20, 0),
('Lavender', 22, 480, 50, 220, 20, 0),
('Peppermint', 18, 540, 40, 180, 20, 0),
('Rubber Plant', 28, 660, 65, 280, 20, 0);

INSERT INTO PlantLog (dateTime, plantID)
VALUES
((datetime(CURRENT_TIMESTAMP, '+1 hour')), 2),
((datetime(CURRENT_TIMESTAMP, '+1 hour')), 1);

INSERT INTO SensorReadout (dateTime, temperature, airHumidity, soilMoisture, doorIsOpen,waterIsOn,lightIsOn)
VALUES ('2024-03-13 08:00:00', 25, 60, 40,0,0,0);

INSERT INTO SensorReadout (dateTime, temperature, airHumidity, soilMoisture, doorIsOpen,waterIsOn,lightIsOn)
VALUES ('2024-03-13 09:00:00', 26, 62, 42,1,0,0);

INSERT INTO SensorReadout (dateTime, temperature, airHumidity, soilMoisture,doorIsOpen,waterIsOn,lightIsOn)
VALUES ('2024-03-13 10:00:00', 27, 64, 45,0,0,0);

INSERT INTO SensorReadout (dateTime, temperature, airHumidity, soilMoisture,doorIsOpen,waterIsOn,lightIsOn)
VALUES ('2024-03-13 11:00:00', 28, 65, 46,0,0,0);

INSERT INTO SensorReadout (dateTime, temperature, airHumidity, soilMoisture,doorIsOpen,waterIsOn,lightIsOn)
VALUES ('2024-03-13 12:00:00', 29, 66, 47,1,0,0);