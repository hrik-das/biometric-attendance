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

// #define USUAL_WORKFLOW
#define EMPTY_DATABASE

#define R307_RX D3
#define R307_TX D4

/// @brief `SoftwareSerial` object to instantiate sensor with.
SoftwareSerial ss = SoftwareSerial(R307_RX, R307_TX);
/// @brief Denotes the R307 Fingerprint Sensor module of the project.
Adafruit_Fingerprint R307 = Adafruit_Fingerprint(&ss);

/// @brief Utility object for holding Template Library location
/// of a user fingerprint to add, update or delete.
u16 loc;
/// @brief Utility object for holding the Operation that needs to be
/// done for a user, if any.
Operation op;
/// @brief Utility object for holding the roll no. of a student
///  whenever needed.
String roll;

/// @brief Global OLED SSD1306 display object for modules to use.
Adafruit_SSD1306 SSD1306(128, 64);

/// @brief WiFi network name
const char *ssid = 
"bigdaddyroy";
/// @brief WiFi network password
const char *passphrase = 
"54e74ffded91q";

/// @brief Fully qualified resource path of remote server
const String send_url = 
"http://192.168.121.189:80/bas/getdata.php";

#if defined(USUAL_WORKFLOW)

void setup() {
    Serial.begin(115200);
    Serial.print(F("\r\n\nesp.ino:setup"));

    Serial.print(F("Configured WiFi: ")); Serial.println(ssid);
    Serial.print(F("Configured remote server: ")); Serial.println(send_url);

    // Address 0x3D for 128x64
    if (!SSD1306.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 Allocation failed."));
        while (1) delay(2000);
    }

    // Show Adafruit Logo for 2 seconds at startup
    SSD1306.display();
    delay(2000);
    SSD1306.clearDisplay();
    SSD1306.display();
    
    // Default settings for the OLED display
    SSD1306.setTextColor(WHITE);
    SSD1306.setTextSize(2);
    SSD1306.setTextWrap(true);

    join_wifi();

    R307.begin(57600);
    
    Serial.println(F("Fingerprint Sensor detection test."));
    // A good way to check if the sensor is active and responding
    if (!R307.verifyPassword()) {
        // Sensor is not active or repsonding
        Serial.println(F("Did not find fingerprint sensor"));
        showLocalErrorMsg();
        while (1) delay(1);
    }

    // Sensor is active and responding
    Serial.println(F("Found fingerprint sensor!"));
    SSD1306.clearDisplay();
    SSD1306.setCursor(28, 10); SSD1306.print(F("SENSOR"));
    SSD1306.setCursor(34, 38); SSD1306.print(F("FOUND"));
    SSD1306.display();
    
    Serial.print(__FILE__); Serial.print(':'); Serial.print(__LINE__ + 3);
    Serial.print(F(":getTemplateCount:"));

    switch (R307.getTemplateCount()) {
        case FINGERPRINT_OK:
            Serial.println(F("FINGERPRINT_OK"));
            draw64x64Bitmap(FOUND);
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            while(1) delay(2000);
        default:
            Serial.println(F("<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            while(1) delay(2000);
    }

    Serial.print(__FILE__); Serial.print(':'); Serial.print(__LINE__ + 1);
    Serial.println(":templateCount:"); Serial.println(R307.templateCount);
    
    delay(1000);
}

void loop() {
    Serial.println(F("esp.ino:loop"));

    // ==============================================================
    // USER LOG (ATTENDANCE)
    // ==============================================================
    // portion of code which "actually checks for attendance"
    // `sensor` will hold the matched user fingerprint
    if (check_for_valid_user(&R307) == FINGERPRINT_OK)
        // enlisted user pressed thumb, and fingerprints were
        // matched with the template library (or the fingerprint
        // sensor database)
        log_user(R307);

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
            case Operation::Enlist: return enlist(loc, R307);
            case Operation::Delist: return delist(loc, R307);
            case Operation::Update: return update(loc, R307);
            default: std::abort();
            }} (op);
        
        confirm_EDU(op, loc, roll, result);
    }
}

#elif defined(EMPTY_DATABASE)

void setup() {
    Serial.begin(115200);
    Serial.println(F("\r\n\nesp.ino:setup"));

    R307.begin(57600);
    
    Serial.println(F("Fingerprint Sensor detection test."));
    // A good way to check if the sensor is active and responding
    if (!R307.verifyPassword()) {
        // Sensor is not active or repsonding
        Serial.println(F("Did not find fingerprint sensor"));
        showLocalErrorMsg();
        // goto setupexit;
        return;
    }

    Serial.println(F("Found fingerprint sensor!"));
    
    Serial.print(__FILE__); Serial.print(':'); Serial.print(__LINE__ + 3);
    Serial.print(F(":getTemplateCount:"));

    switch (R307.getTemplateCount()) {
        case FINGERPRINT_OK:
            Serial.println(F("FINGERPRINT_OK"));
            draw64x64Bitmap(FOUND);
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
        default:
            Serial.println(F("<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
    }

    // R307.getTemplateCount();

    Serial.print(__FILE__ + ':' + __LINE__ + 1);
    Serial.println(":templateCount:"); Serial.println(R307.templateCount);

    Serial.print(__FILE__); Serial.print(':'); Serial.print(__LINE__ + 3);
    Serial.print(F(":emptyDatabase:"));

    switch (R307.emptyDatabase()) {
        case FINGERPRINT_OK:
            Serial.println(F("FINGERPRINT_OK"));
            draw64x64Bitmap(FOUND);
            break;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F("FINGERPRINT_BADLOCATION"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
        case FINGERPRINT_FLASHERR:
            Serial.println(F("FINGERPRINT_FLASHERR"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
        default:
            Serial.println(F("<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
    }

    // R307.emptyDatabase();

    Serial.println(F("Done."));
    
    Serial.print(__FILE__); Serial.print(':'); Serial.print(__LINE__ + 3);
    Serial.print(F(":getTemplateCount:"));

    switch (R307.getTemplateCount()) {
        case FINGERPRINT_OK:
            Serial.println(F("FINGERPRINT_OK"));
            draw64x64Bitmap(FOUND);
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
        default:
            Serial.println(F("<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            // goto setupexit;
            break;
            // return;
    }

    // R307.getTemplateCount();

    Serial.print(__FILE__); Serial.print(':'); Serial.print(__LINE__ + 3);
    Serial.println(":templateCount:");
    
    Serial.println(R307.templateCount);

// setupexit:
}

void loop() {}

#endif
