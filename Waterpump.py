import RPi.GPIO as GPIO # Für das ansteuern der GPIO

GPIO.setwarnings(False)
GPIO.setmode(GPIO.BCM)

PinWaterPump = 20
GPIO.setup(PinWaterPump, GPIO.OUT)

runWaterpump()
GPIO.output(PinWaterPump, GPIO.LOW)

def runWaterpump():
    if (GPIO.input(PinWaterPump)):
        GPIO.output(PinWaterPump, GPIO.HIGH) #Schalte die Wasserpumpe ein
    else:
        GPIO.output(PinWaterPump, GPIO.LOW) # Schalte die Wasserpupe aus
    return
