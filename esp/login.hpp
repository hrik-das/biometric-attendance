#ifndef _LOGIN_HPP
#define _LOGIN_HPP 1

#include <WiFiClient.h>
#include <ESP8266HTTPClient.h>
#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>

#include "connection.hpp"
#include "boilerplate.hpp"
#include "bitmap.h"

extern Adafruit_SSD1306 display;
extern const char *send_url;

/// @brief Ask sensor to take image, convert to feature template, search for
/// feature template to match saved templates, and return matching location. The
/// matching location is stored in `sensor.fingerID` and the matching confidence
/// in `sensor.confidence`.
/// @param sensor `[OUT]` Object denoting a fingerprint sensor. Supply or
/// make available in caller scope. Should have same name as param.
/// @return `FINGERPRINT_OK` on fingerprint match success. Not `FINGERPRINT_OK`
/// on failure.
uint8_t check_for_valid_user(Adafruit_Fingerprint *sensor)
{
    Serial.println(F("login.hpp:check_for_valid_user"));

    switch (sensor->getImage()) {
        case FINGERPRINT_OK:
            break;
        case FINGERPRINT_NOFINGER:
            draw64x64Bitmap(SYS_IDLE);
            return FINGERPRINT_NOFINGER;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:28:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
        case FINGERPRINT_IMAGEFAIL:
            Serial.println(F("login.hpp:28:FINGERPRINT_IMAGEFAIL"));
            showLocalErrorMsg();
            return FINGERPRINT_IMAGEFAIL;
    }

    switch (sensor->image2Tz()) {
        case FINGERPRINT_OK:
            break;
        case FINGERPRINT_IMAGEMESS:
            draw64x64Bitmap(NOT_FOUND);
            return FINGERPRINT_IMAGEMESS;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:44:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
        case FINGERPRINT_FEATUREFAIL:
            Serial.println(F("login.hpp:44:FINGERPRINT_FEATUREFAIL"));
            showLocalErrorMsg();
            return FINGERPRINT_FEATUREFAIL;
        case FINGERPRINT_INVALIDIMAGE:
            Serial.println(F("login.hpp:44:FINGERPRINT_INVALIDIMAGE"));
            showLocalErrorMsg();
            return FINGERPRINT_INVALIDIMAGE;
    }
    
    switch (sensor->fingerFastSearch()) {
        case FINGERPRINT_OK:
            draw64x64Bitmap(FOUND);
            return FINGERPRINT_OK;
        case FINGERPRINT_NOTFOUND:
            draw64x64Bitmap(NOT_FOUND);
            return FINGERPRINT_NOTFOUND;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("login.hpp:64:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
    }

    // to suppress `control reaches end of non-void
    // function` warning (`-Werror=return-type`)
    // will never run
    std::abort();
} // uint8_t check_for_valid_user(Adafruit_Fingerprint *sensor)

/// @brief Sends matched template location to configured `send_url`. Prints Roll
/// No. of associated user, otherwise `REMOTE ERROR`.
/// @param sensor `[IN]` Same object that was previously passed to `uint8_t
/// check_for_valid_user (Adafruit_Fingerprint &sensor)`.
/// Supply or make available in caller scope. Should have same name as param.
void log_user(const Adafruit_Fingerprint &sensor)
{
    Serial.println(F("login.hpp:log_user"));
    
    WiFiClient client;
    HTTPClient http;
    
    // [sensor library : server database] `Fingerprint_ID` (Template
    // Location) mapping is [`N`: `N + 1`]
    String postData = "login=" + String(sensor.fingerID + 1);
    
    verify_conn();

    http.begin(client, send_url);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpCode = http.POST(postData);
    String payload = http.getString();
    http.end();

    Serial.print(F("Logging user at location: ")); Serial.println(sensor.fingerID);
    Serial.print(F("Request payload:\n")); Serial.println(postData);
    Serial.print(F("HTTP return code: ")); Serial.println(httpCode);

    display.clearDisplay();

    if (payload.isEmpty()) {
        display.setCursor(28, 10); display.print(F("REMOTE"));
        display.setCursor(34, 38); display.print(F("ERROR"));
        Serial.println(F("Response payload empty! [REMOTE ERROR]"));
    } else {
        display.setCursor(0, 0);
        display.print(F("WELCOME")); display.setCursor(0, 20); display.setTextSize(3);
        display.print(payload);
        // resetting to default text size
        display.setTextSize(2);
        Serial.print(F("Response payload [Roll No.]: ")); Serial.println(payload);
    }

    display.display();
    delay(1000);
} // void log_user(const Adafruit_Fingerprint &sensor)

#endif // _LOGIN_HPP
