#ifndef _LOG_USER_HPP
#define _LOG_USER_HPP 1

#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>

#include "connection.hpp"
#include "http_post.hpp"
#include "visuals.hpp"
#include "bitmap.h"

extern Adafruit_SSD1306 display;
extern String roll;

/// @brief Ask sensor to take image, convert to feature template, search for
/// feature template to match saved templates, and return matching location. The
/// matching location is stored in `sensor.fingerID` and the matching confidence
/// in `sensor.confidence`.
/// @param sensor `[OUT]` Object denoting a fingerprint sensor. Supply or
/// make available in caller scope. Should have same name as param.
/// @return `FINGERPRINT_OK` on fingerprint match success. Not `FINGERPRINT_OK`
/// on failure.
u8 check_for_valid_user(Adafruit_Fingerprint *sensor)
{
    Serial.println(F("login.hpp:check_for_valid_user"));

    switch (sensor->getImage()) {
        case FINGERPRINT_OK:
            break;
        case FINGERPRINT_NOFINGER:
            bas::draw64x64Bitmap(SYS_IDLE);
            return FINGERPRINT_NOFINGER;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:27:FINGERPRINT_PACKETRECIEVEERR"));
            bas::showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
        case FINGERPRINT_IMAGEFAIL:
            Serial.println(F("login.hpp:27:FINGERPRINT_IMAGEFAIL"));
            bas::showLocalErrorMsg();
            return FINGERPRINT_IMAGEFAIL;
    }

    switch (sensor->image2Tz()) {
        case FINGERPRINT_OK:
            break;
        case FINGERPRINT_IMAGEMESS:
            bas::draw64x64Bitmap(NOT_FOUND);
            return FINGERPRINT_IMAGEMESS;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:43:FINGERPRINT_PACKETRECIEVEERR"));
            bas::showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
        case FINGERPRINT_FEATUREFAIL:
            Serial.println(F("login.hpp:43:FINGERPRINT_FEATUREFAIL"));
            bas::showLocalErrorMsg();
            return FINGERPRINT_FEATUREFAIL;
        case FINGERPRINT_INVALIDIMAGE:
            Serial.println(F("login.hpp:43:FINGERPRINT_INVALIDIMAGE"));
            bas::showLocalErrorMsg();
            return FINGERPRINT_INVALIDIMAGE;
    }
    
    switch (sensor->fingerFastSearch()) {
        case FINGERPRINT_OK:
            bas::draw64x64Bitmap(FOUND);
            return FINGERPRINT_OK;
        case FINGERPRINT_NOTFOUND:
            bas::draw64x64Bitmap(NOT_FOUND);
            return FINGERPRINT_NOTFOUND;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:63:FINGERPRINT_PACKETRECIEVEERR"));
            bas::showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
    }

    // to suppress `control reaches end of non-void
    // function` warning (`-Werror=return-type`)
    // will never run
    std::abort();
} // u8 check_for_valid_user(Adafruit_Fingerprint *sensor)

/// @brief Sends matched template location to configured `send_url`. Prints Roll
/// No. of associated user, otherwise `REMOTE ERROR`. Call only if `u8
/// check_for_valid_user (Adafruit_Fingerprint *sensor)` returns `FINGERPRINT_OK`.
/// @param sensor `[IN]` Same object that was previously passed to `u8
/// check_for_valid_user (Adafruit_Fingerprint &sensor)`.
/// Supply or make available in caller scope. Should have same name as param.
void log_user(const Adafruit_Fingerprint &sensor)
{
    Serial.println(F("login.hpp:log_user"));
    
    // [sensor library : server database] `Fingerprint_ID` (Template
    // Location) mapping is [`N`: `N + 1`]
    String postData = "log-user-at=" + String(sensor.fingerID + 1);
    
    verify_conn();

    String payload = bas::httpPOST(postData);

    Serial.print(F("Logging user at location: ")); Serial.println(sensor.fingerID);

    display.clearDisplay();

    if (payload.isEmpty()) {
        display.setCursor(27, 10); display.print(F("REMOTE"));
        display.setCursor(34, 38); display.print(F("ERROR"));
        Serial.println(F("Response payload empty: REMOTE ERROR"));
    } else {
        roll = payload.substring(payload.indexOf('R') + 1);

        display.setCursor(22, 6); display.print(F("WELCOME"));
        display.setTextSize(3);
        display.setCursor((128 - 18 * (roll.length() + 1)) >> 1, 38);
        display.print(F("#")); display.print(roll);
        display.setTextSize(2);
    }

    display.display();
    delay(1000);
} // void log_user(const Adafruit_Fingerprint &sensor)

#endif // _LOG_USER_HPP
