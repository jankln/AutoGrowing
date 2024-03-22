import os

def reboot_system():
    # Benutzer braucht SUDO rechte

    # windows
    #os.system('shutdown /r /t 1')

    #linux
    os.system('sudo reboot')

if __name__ == "__main__":
    reboot_system()
