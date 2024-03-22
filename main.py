import smbus2  # I2C-Kommunikation
import bme280  # BME280-Sensor-Funktionalität
import sqlite3  # Datenbank Integration
import time as t  # Zeitverzögerungen
import datetime  # UnixTime
import Adafruit_ADS1x15  # Für die ADC to I2C Schnittstelle / Bodenfeuchtigkeitssensor
import RPi.GPIO as GPIO  # Für das ansteuern der GPIO
import threading  # Für Multithreading


GPIO.setwarnings(False)
GPIO.setmode(GPIO.BCM) # GPIO-Nummerierungsschema : https://raspi.tv/2013/rpi-gpio-basics-4-setting-up-rpi-gpio-numbering-systems-and-inputs

# BME280
address = 0x77  # I2C-Adresse des BME280-Sensors
bus_number = 1  # I2C-Busnummer (1 bei Raspberry Pi 3B+)

bus = smbus2.SMBus(bus_number)  # Öffnen des I2C-Busses
calibrationParameter = bme280.load_calibration_params(bus, address)  # Laden der Kalibrierungsparameter des Sensors

# Bodenfeuchtigkeit Sensor / ADC to I2C
adc = Adafruit_ADS1x15.ADS1015()
GAIN = 1  # 1 = Analog kann zwischen 0-4.096V sein: https://joy-it.net/en/products/SEN-Moisture


# Wasserpumpe
PinWaterPump = 20
waterpumpStatus = False
waterpumpLastAktive = None
GPIO.setup(PinWaterPump, GPIO.OUT)

# Servomotor/ lüftung
servoStatus = False
PinServomotor = 26
servomotorLastAktive = None
GPIO.setup(PinServomotor, GPIO.OUT)
pwm = GPIO.PWM(PinServomotor, 50)

# Beleuchtung
PinLight = 10
isLightInit = False
GPIO.setup(PinLight, GPIO.OUT)
lichtStatus = False

# sqllite
sqliteConnection = sqlite3.connect("AutoGrowing.db")
cursor = sqliteConnection.cursor()

plantData = None
currentPlantID = None

#Datenbankabfrage, welches Pflanzenprofil gerade aktiv ist und welche Daten es enthält.
def InitialSetup():
    global currentPlantID, plantData

    sqlGetPlantID = "SELECT plantID FROM PlantLog Order by dateTime DESC Limit 1;"
    sqlGetPlantData = "SELECT growingTemperature, lightDurationInMinutesPerDay, growingSoilMoisture, growingAirHumidity, waterRequirementPerDay, actOnSoilMoisture FROM Plant WHERE plantID = ?;"

    cursor.execute(sqlGetPlantID)
    (currentPlantID,) = cursor.fetchone()
    if currentPlantID:
        cursor.execute(sqlGetPlantData, (currentPlantID,))
        row = cursor.fetchone()
        if row:
            plantData = dict(
                zip(
                    [
                        "growingTemperature",
                        "lightDurationInMinutesPerDay",
                        "growingSoilHumidity",
                        "humidityTreshold",
                        "waterRequirementPerDay",
                        "actOnSoilMoisture",
                    ],
                    row,
                )
            )
        else:
            plantData = None
    else:
        currentPlantID = None
        plantData = None

    # Konsolen ausgabe
    for key, value in plantData.items():
        print(f"{key} {value}")
    return


# Auslesen der Daten des BME280 Sensors
# Rückgabe der Werte in Form eines Arrays
def bme280_read():
    data = bme280.sample(bus, address, calibrationParameter)
    bmeReadouts = [
        round(data.temperature, 2),
        round(data.humidity, 2),
        round(data.pressure, 2),
    ]
    return (
        bmeReadouts
    )

# Einfügen der Sensorwerte in die Datenbank
def insertIntoTable(
    bme280Readouts, soilMoisture, servoStatus, waterpumpStatus, lichtStatus
):
    dateTime = datetime.datetime.now()
    query = "INSERT INTO sensorReadout (dateTime, temperature, airHumidity, soilMoisture, doorIsOpen, waterIsOn, lightIsOn) values (?,?,?,?,?,?,?)"
    cursor.execute(
        query,
        (
            str(dateTime),
            str(bme280Readouts[0]),
            str(bme280Readouts[1]),
            str(soilMoisture),
            str(servoStatus),
            str(waterpumpStatus),
            str(lichtStatus),
        ),
    )
    sqliteConnection.commit()

# Betrieb der Wasserpumpe
def runWaterpump(time):
    global waterpumpStatus, waterpumpLastAktive
    if GPIO.input(PinWaterPump) or waterpumpStatus:
        return
    waterpumpLastAktive = datetime.datetime.now()
    waterpumpStatus = True
    GPIO.output(PinWaterPump, GPIO.HIGH)  # Schalte die Wasserpumpe ein
    t.sleep(time)
    GPIO.output(PinWaterPump, GPIO.LOW)  # Schalte die Wasserpupe aus
    waterpumpStatus = False
    return

def SwitchLight():
    if (isLightInit):
        return
    l
    isLightInit = True
    if (GPIO.input(PinLight)):
        lichtStatus = True
        GPIO.output(PinLight, GPIO.HIGH)
    else:
        lichtStatus = False
        GPIO.output(PinWaterPump, GPIO.LOW)
    t.sleep(300)
    isLightInit = False
    return

# Betrieb des Servomotors
def servoMotor(time):
    global servoStatus, servomotorLastAktive
    if GPIO.input(PinServomotor) or servoStatus:
        return

    servomotorLastAktive = datetime.datetime.now()
    servoStatus = True

    duty = (180 / 18) + 2
    pwm.start(duty)
    t.sleep(0.5)
    pwm.stop
    t.sleep(time)

    pwm.start(duty)
    duty = (1 / 18) + 2
    pwm.start(duty)
    t.sleep(0.5)
    pwm.start(0)
    pwm.stop

    servoStatus = False
    return

# Hauptcode, der den Codefluss steuert
try:
    while True:
        InitialSetup()

        print("\nongoing")

        # BME280
        bme280Readouts = bme280_read()

        # Bodenfeuchtigkeit/ADC to i2c
        soilMoisture = adc.read_adc(0, gain=GAIN)

        insertIntoTable(
            bme280Readouts, soilMoisture, servoStatus, waterpumpStatus, lichtStatus
        )
        print("Insert into table")
        print(
            f"\nBME: {bme280Readouts}\nSoil Moisture: {soilMoisture}\nServo: {servoStatus}\nWaterpump: {waterpumpStatus}\nlight: {lichtStatus}"
        )

        # Run water pump and servo motor in separate threads
        water_pump_thread = threading.Thread(target=runWaterpump, args=[plantData["waterRequirementPerDay"]])
        servo_motor_thread = threading.Thread(target=servoMotor, args=[12])
        switch_light_thread = threading.Thread(target=SwitchLight)
        if waterpumpLastAktive is None:
            waterpumpShouldWork = True
        else:
            waterpumpShouldWork = (datetime.datetime.now() - waterpumpLastAktive).days >= 1

        if servomotorLastAktive is None:
            ServoMotorShouldWork = True
        else:
            ServoMotorShouldWork = bme280Readouts[1] >= plantData["humidityTreshold"]

        if not waterpumpStatus and waterpumpShouldWork:
            print("start waterpump")
            water_pump_thread.start()

        if not servoStatus and ServoMotorShouldWork:
            print("start servo")
            servo_motor_thread.start()

        currentTime = datetime.datetime.now().time()
        if not isLightInit and currentTime >= datetime.time(10, 0) and currentTime <= datetime.time(10, 1):
            print("switch light")
            switch

        t.sleep(10)  # 2 Sekunden vor der nächsten Messung

except KeyboardInterrupt:
    print(
        "Messung wurde vom Benutzer gestoppt."
    )  # Ausgabe, wenn der Benutzer die Messung abbricht

finally:
    GPIO.output(PinWaterPump, GPIO.LOW)
    pwm.stop()
    GPIO.output(PinLight, GPIO.LOW)

    bus.close()  # Schließen des I2C-Busses nach Beendigung der Messung
    sqliteConnection.close()
    GPIO.cleanup() 
