#ifndef _LOG_USER_HPP
#define _LOG_USER_HPP 1

#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>

#include "connection.hpp"
#include "http_post.hpp"
#include "visuals.hpp"
#include "bitmap.h"

extern Adafruit_SSD1306 SSD1306;
extern String roll;

/// @brief Ask sensor to take image, convert to feature template, search for
/// feature template to match saved templates, and return matching location. The
/// matching location is stored in `R307.fingerID` and the matching confidence
/// in `R307.confidence`.
/// @param R307 `[OUT]` Object denoting the R307 fingerprint sensor.
/// @return `FINGERPRINT_OK` on fingerprint match success. Not `FINGERPRINT_OK`
/// on failure.
u8 check_for_valid_user(Adafruit_Fingerprint *R307)
{
    Serial.println(F("login.hpp:check_for_valid_user"));

    u8 temp = R307->getImage();
    Serial.print(F("getImage(): ")); Serial.println(temp);

    switch (temp) {
        case FINGERPRINT_OK:
            break;
        case FINGERPRINT_NOFINGER:
            draw64x64Bitmap(SYS_IDLE);
            return temp;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:26:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_IMAGEFAIL:
            Serial.println(F("login.hpp:26:FINGERPRINT_IMAGEFAIL"));
            showLocalErrorMsg();
            return temp;
        default:
            Serial.println(F("[UNDOCUMENTED]"));
            showLocalErrorMsg();
            return temp;
    }

    temp = R307->image2Tz();
    Serial.print(F("image2Tz(): ")); Serial.println(temp);

    switch (temp) {
        case FINGERPRINT_OK:
            break;
        case FINGERPRINT_IMAGEMESS:
            draw64x64Bitmap(NOT_FOUND);
            return temp;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:49:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_FEATUREFAIL:
            Serial.println(F("login.hpp:49:FINGERPRINT_FEATUREFAIL"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_INVALIDIMAGE:
            Serial.println(F("login.hpp:49:FINGERPRINT_INVALIDIMAGE"));
            showLocalErrorMsg();
            return temp;
        default:
            Serial.println(F("[UNDOCUMENTED]"));
            showLocalErrorMsg();
            return temp;
    }

    temp = R307->fingerFastSearch();
    Serial.print(F("fingerFastSearch(): ")); Serial.println(temp);

    switch (temp) {
        case FINGERPRINT_OK:
            draw64x64Bitmap(FOUND);
            return temp;
        case FINGERPRINT_NOTFOUND:
            draw64x64Bitmap(NOT_FOUND);
            return temp;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:76:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return temp;
        default:
            Serial.println(F("[UNDOCUMENTED]"));
            showLocalErrorMsg();
            return temp;
    }
} // u8 check_for_valid_user(Adafruit_Fingerprint *R307)

/// @brief Sends matched template location to configured `send_url`. Prints Roll
/// No. of associated user, otherwise `REMOTE ERROR`. Call only if `u8
/// check_for_valid_user (Adafruit_Fingerprint *R307)` returns `FINGERPRINT_OK`.
/// @param R307 `[IN]` Same object that was previously passed to `u8
/// check_for_valid_user (Adafruit_Fingerprint &R307)`.
void log_user(const Adafruit_Fingerprint &R307)
{
    Serial.println(F("login.hpp:log_user"));
    
    // [R307 library : server database] `Fingerprint_ID` (Template
    // Location) mapping is [`N`: `N + 1`]
    String postData = "log-user-at=" + String(R307.fingerID + 1);
    
    verify_conn();

    String payload = httpPOST(postData);

    Serial.print(F("Logging user at location: ")); Serial.println(R307.fingerID);

    SSD1306.clearDisplay();

    if (payload.isEmpty()) {
        SSD1306.setCursor(28, 10); SSD1306.print(F("REMOTE"));
        SSD1306.setCursor(34, 38); SSD1306.print(F("ERROR"));
        Serial.println(F("Response payload empty: REMOTE ERROR"));
    } else {
        roll = payload.substring(payload.indexOf('R') + 1);

        SSD1306.setCursor(22, 6); SSD1306.print(F("WELCOME"));
        SSD1306.setTextSize(3);
        SSD1306.setCursor((128 - 18 * (roll.length() + 1)) >> 1, 38);
        SSD1306.print(F("#")); SSD1306.print(roll);
        SSD1306.setTextSize(2);
    }

    SSD1306.display();
    delay(1000);
} // void log_user(const Adafruit_Fingerprint &sensor)

#endif // _LOG_USER_HPP
