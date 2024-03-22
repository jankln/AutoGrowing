PinServoMotor = 26
GPIO.setup(PinServoMotor, GPIO.OUT)
pwm = GPIO.PWM(PinServoMotor, 50)

servoMotor()

def servoMotor():
    if (GPIO.input(PinServoMotor)):
        duty = 2
        pwm.start(duty)
        t.sleep(0.5)
        pwm.start(0)
        pwm.stop
    else:
        duty = (180 / 18) + 2
        pwm.start(duty)
        t.sleep(0.5)
        pwm.start(0)
        pwm.stop
    return