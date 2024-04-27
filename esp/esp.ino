#include <SoftwareSerial.h>
#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>
#include <pins_arduino.h>

#include "connection.hpp"
#include "login.hpp"
#include "enlist.hpp"
#include "delist.hpp"
#include "update.hpp"
#include "remote.hpp"
#include "boilerplate.hpp"
#include "bitmap.h"

#define FINGERPRINT_RX D3
#define FINGERPRINT_TX D4

/// @brief `SoftwareSerial` object to instantiate sensor with.
SoftwareSerial ss = SoftwareSerial(FINGERPRINT_RX, FINGERPRINT_TX);
/// @brief Denotes the R307 Fingerprint Sensor module of the project.
Adafruit_Fingerprint sensor = Adafruit_Fingerprint(&ss);

/// @brief Utility object for holding Template Library location
/// of a user fingerprint to add, update or delete.
uint16_t loc;

/// @brief Global OLED display object for modules to use.
Adafruit_SSD1306 display(128, 64);

/// @brief WiFi network name
const char *ssid = "";
/// @brief WiFi network password
const char *passphrase = "";

/// @brief Fully qualified resource path of remote server
const char *send_url = "http://playbahn-arch/bas/getdata.php";

void setup() {
    Serial.println(F("esp.ino:setup"));
    Serial.begin(115200);

    // Address 0x3D for 128x64
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 allocation failed"));
        while (1) delay(1);
    }

    // Show Adafruit Logo for 2 seconds at startup
    display.display();
    delay(2000);
    display.clearDisplay();
    
    // Default settings for the OLED display
    display.setTextColor(WHITE);
    display.setTextSize(2);
    display.setTextWrap(true);

    join_wifi();

    sensor.begin(57600);
    
    Serial.println(F("Fingerprint Sensor detection test."));
    // A good way to check if the sensor is active and responding
    if (!sensor.verifyPassword()) {
        // Sensor is not active or repsonding
        Serial.println(F("Did not find fingerprint sensor"));
        showLocalErrorMsg();
        while (1) delay(1);
    }

    // Sensor is active and responding
    Serial.println(F("Found fingerprint sensor!"));
    draw64x64Bitmap(FOUND);

    sensor.getTemplateCount();
    Serial.print(F("Templates in sensor: "));
    Serial.println(sensor.templateCount);
}

void loop() {
    Serial.println(F("esp.ino:loop"));

    // ==============================================================
    // USER LOG (ATTENDANCE)
    // ==============================================================
    // portion of code which "actually checks for attendance"
    // `sensor` will hold the matched user fingerprint
    if (check_for_valid_user(&sensor) == FINGERPRINT_OK)
        // enlisted user pressed thumb, and fingerprints were
        // matched with the template library (or the fingerprint
        // sensor database)
        log_user(sensor);

    delay(50);
    
    // ==============================================================
    // USER ENLIST
    // ==============================================================
    // check with the remote server if a user needs to be enlisted
    // (or from the microcontroller's POV, their fingerprint needs
    // to be added to the template library)
    // `loc` will hold Location of template library where user
    //  fingerprint is to be stored
    if (check_action(Action::Enlist, &loc)) {
        // fingerprint needs to added, try to create and store user
        // fingerprint at location `loc` in template library
        if (enlist(loc, sensor) == FINGERPRINT_OK) {
            // user enlisted successfully, send success message
            confirm_action(Action::Enlist, loc, true);
        } else {
            // user NOT enlisted successfully, send failure message
            confirm_action(Action::Enlist, loc, false);
        }
    }

    // ==============================================================
    // USER DELIST
    // ==============================================================
    // check with the remote server if a user needs to be delisted
    // (or from the microcontroller's POV, their fingerprint needs
    //  to be deleted from template library)
    // `loc` will hold Location of template library from where user
    //  fingerprint is to be deleted
    if (check_action(Action::Delist, &loc)) {
        // fingerprint needs to deleted, try to delete user
        // fingerprint from location `loc` in template library
        if (delist(loc, sensor) == FINGERPRINT_OK) {
            // user delisted successfully, send success message
            confirm_action(Action::Delist, loc, true);
        } else {
            // user NOT delisted successfully, send failure message
            confirm_action(Action::Delist, loc, false);
        }
    }

    // ==============================================================
    // USER UPDATE
    // ==============================================================
    // check with the remote server if a user needs to be updated
    // (or from the microcontroller's POV, their old fingerprint
    // needs to be deleted from template library and a new one is to
    // be created and stored)
    // `loc` will hold Location of template library at where user
    //  fingerprint is to be updated
    if (check_action(Action::Update, &loc)) {
        // fingerprint needs to update, try to update user
        // fingerprint at location `loc` in template library
        if (update(loc, sensor) == FINGERPRINT_OK) {
            // user update successfully, send success message
            confirm_action(Action::Update, loc, true);
        } else {
            // user NOT updated successfully, send failure message
            confirm_action(Action::Update, loc, false);
        }
    }
}
