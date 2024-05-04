#include <SoftwareSerial.h>
#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>
#include <pins_arduino.h>

#include "connection.hpp"
#include "log_user.hpp"
#include "enlist.hpp"
#include "delist.hpp"
#include "update.hpp"
#include "remote.hpp"
#include "visuals.hpp"
#include "bitmap.h"

#define FINGERPRINT_RX D3
#define FINGERPRINT_TX D4

/// @brief `SoftwareSerial` object to instantiate sensor with.
SoftwareSerial ss = SoftwareSerial(FINGERPRINT_RX, FINGERPRINT_TX);
/// @brief Denotes the R307 Fingerprint Sensor module of the project.
Adafruit_Fingerprint sensor = Adafruit_Fingerprint(&ss);

/// @brief Utility object for holding Template Library location
/// of a user fingerprint to add, update or delete.
u16 loc;
/// @brief Utility object for holding the Operation that needs to be
/// done for a user, if any.
Operation op;
/// @brief Utility object for holding the roll no. of a student
///  whenever needed.
String roll;

/// @brief Global OLED display object for modules to use.
Adafruit_SSD1306 display(128, 64);

/// @brief WiFi network name
const char *ssid = 
"";
/// @brief WiFi network password
const char *passphrase = 
"";

/// @brief Fully qualified resource path of remote server
const String send_url = 
"";

void setup() {
    Serial.println(F("esp.ino:setup"));
    Serial.begin(115200);

    // Address 0x3D for 128x64
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 Allocation failed."));
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
        bas::showLocalErrorMsg();
        while (1) delay(1);
    }

    // Sensor is active and responding
    Serial.println(F("Found fingerprint sensor!"));
    bas::draw64x64Bitmap(FOUND);

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
    // SPECIAL OPERATIONS - ENLIST / DELIST / UPDATE
    // ==============================================================
    // check with the remote server if a user needs to be either
    // enlisted, delisted, or updated (or from the microcontroller's
    // POV, their fingerprint needs to be added to, deleted, or
    // updated from the template library)
    if (check_EDU(&op, &loc, &roll)) {
        // An add, delete or update operation needs to be done
        u8 result = [] (Operation op) { switch (op) {
            case Operation::Enlist: return enlist(loc, sensor);
            case Operation::Delist: return delist(loc, sensor);
            case Operation::Update: return update(loc, sensor);
            default: std::abort();
            }} (op);
        
        confirm_EDU(op, loc, roll, result);
    }
}
